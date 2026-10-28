<?php
/**
 * GSLEY Security — two-factor authentication (TOTP, RFC 6238).
 *
 * Works with any authenticator app (Google Authenticator, Microsoft
 * Authenticator, 1Password, Authy…). Setup lives on the user's profile:
 * scan nothing — enter the key shown, confirm with a code, and save the ten
 * one-time backup codes. Login then asks for the 6-digit code (or a backup
 * code) on the normal login form.
 *
 * - The TOTP secret is encrypted at rest (AES-256-GCM, key derived from the
 *   site's secret keys in wp-config.php).
 * - Backup codes are stored only as password hashes and work once.
 * - A code can't be reused within its time window (replay protection).
 * - Administrators must enrol (setting on by default): until they do, wp-admin
 *   sends them to their profile to finish setup.
 * - Failed codes count towards the login lockout.
 * - Emergency bypass: define( 'GSLEY_SECURITY_DISABLE_2FA', true ) in wp-config.php.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gsley_2fa_disabled_globally() {
	return defined( 'GSLEY_SECURITY_DISABLE_2FA' ) && GSLEY_SECURITY_DISABLE_2FA;
}

/* ============================================================ CRYPTO HELPERS */

function gsley_2fa_base32_encode( $bin ) {
	$alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$bits  = '';
	foreach ( str_split( $bin ) as $c ) {
		$bits .= str_pad( decbin( ord( $c ) ), 8, '0', STR_PAD_LEFT );
	}
	$out = '';
	foreach ( str_split( $bits, 5 ) as $chunk ) {
		$out .= $alpha[ bindec( str_pad( $chunk, 5, '0', STR_PAD_RIGHT ) ) ];
	}
	return $out;
}

function gsley_2fa_base32_decode( $b32 ) {
	$alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$b32   = strtoupper( preg_replace( '/[^A-Za-z2-7]/', '', (string) $b32 ) );
	$bits  = '';
	foreach ( str_split( $b32 ) as $c ) {
		$bits .= str_pad( decbin( strpos( $alpha, $c ) ), 5, '0', STR_PAD_LEFT );
	}
	$out = '';
	foreach ( str_split( $bits, 8 ) as $byte ) {
		if ( 8 === strlen( $byte ) ) {
			$out .= chr( bindec( $byte ) );
		}
	}
	return $out;
}

/** 6-digit TOTP for a time step. */
function gsley_2fa_code( $secret_b32, $step ) {
	$key  = gsley_2fa_base32_decode( $secret_b32 );
	$msg  = pack( 'N*', 0 ) . pack( 'N*', $step ); // 64-bit big-endian counter
	$hash = hash_hmac( 'sha1', $msg, $key, true );
	$off  = ord( $hash[19] ) & 0x0f;
	$bin  = ( ( ord( $hash[ $off ] ) & 0x7f ) << 24 ) | ( ( ord( $hash[ $off + 1 ] ) & 0xff ) << 16 ) | ( ( ord( $hash[ $off + 2 ] ) & 0xff ) << 8 ) | ( ord( $hash[ $off + 3 ] ) & 0xff );
	return str_pad( (string) ( $bin % 1000000 ), 6, '0', STR_PAD_LEFT );
}

/**
 * Check a code against ±1 time step. Returns the matched step, or false.
 * Steps at or before $last_step are rejected (replay protection).
 */
function gsley_2fa_verify( $secret_b32, $code, $last_step = 0 ) {
	$code = preg_replace( '/\D/', '', (string) $code );
	if ( 6 !== strlen( $code ) ) {
		return false;
	}
	$now = (int) floor( time() / 30 );
	for ( $i = -1; $i <= 1; $i++ ) {
		$step = $now + $i;
		if ( $step > (int) $last_step && hash_equals( gsley_2fa_code( $secret_b32, $step ), $code ) ) {
			return $step;
		}
	}
	return false;
}

function gsley_2fa_key() {
	$material = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : '' ) . ( defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '' ) . 'gsley-2fa';
	return hash( 'sha256', $material, true );
}

function gsley_2fa_encrypt( $plain ) {
	if ( ! function_exists( 'openssl_encrypt' ) ) {
		return 'plain:' . $plain;
	}
	$iv  = random_bytes( 12 );
	$tag = '';
	$ct  = openssl_encrypt( $plain, 'aes-256-gcm', gsley_2fa_key(), OPENSSL_RAW_DATA, $iv, $tag );
	return 'v1:' . base64_encode( $iv . $tag . $ct ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
}

function gsley_2fa_decrypt( $stored ) {
	$stored = (string) $stored;
	if ( 0 === strpos( $stored, 'plain:' ) ) {
		return substr( $stored, 6 );
	}
	if ( 0 !== strpos( $stored, 'v1:' ) || ! function_exists( 'openssl_decrypt' ) ) {
		return '';
	}
	$raw = base64_decode( substr( $stored, 3 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( false === $raw || strlen( $raw ) < 29 ) {
		return '';
	}
	$plain = openssl_decrypt( substr( $raw, 28 ), 'aes-256-gcm', gsley_2fa_key(), OPENSSL_RAW_DATA, substr( $raw, 0, 12 ), substr( $raw, 12, 16 ) );
	return false === $plain ? '' : $plain;
}

/* ============================================================ USER STATE */

function gsley_2fa_enabled( $user_id ) {
	// A secret that no longer decrypts (site salts rotated) counts as not enrolled,
	// so the user is asked to set two-factor up again instead of being locked out.
	return ! gsley_2fa_disabled_globally() && '1' === get_user_meta( $user_id, '_gsley_2fa_enabled', true ) && '' !== gsley_2fa_secret( $user_id );
}

function gsley_2fa_secret( $user_id ) {
	return gsley_2fa_decrypt( get_user_meta( $user_id, '_gsley_2fa_secret', true ) );
}

/** Check a TOTP or backup code for a user; consumes backup codes and records the step. */
function gsley_2fa_check_user_code( $user_id, $code ) {
	$code = trim( (string) $code );
	if ( '' === $code ) {
		return false;
	}
	$step = gsley_2fa_verify( gsley_2fa_secret( $user_id ), $code, (int) get_user_meta( $user_id, '_gsley_2fa_last', true ) );
	if ( false !== $step ) {
		update_user_meta( $user_id, '_gsley_2fa_last', $step );
		return true;
	}
	// Backup code (one use).
	$norm   = strtolower( preg_replace( '/[^a-z0-9]/i', '', $code ) );
	$hashes = (array) get_user_meta( $user_id, '_gsley_2fa_backup', true );
	foreach ( $hashes as $i => $h ) {
		if ( $h && wp_check_password( $norm, $h ) ) {
			unset( $hashes[ $i ] );
			update_user_meta( $user_id, '_gsley_2fa_backup', array_values( $hashes ) );
			gsley_sec_log( '2fa_backup_used', array( 'user' => get_userdata( $user_id )->user_login, 'detail' => count( $hashes ) . ' left' ), true );
			return true;
		}
	}
	return false;
}

/* ============================================================ LOGIN */

add_action( 'login_form', 'gsley_2fa_login_field' );
function gsley_2fa_login_field() {
	if ( gsley_2fa_disabled_globally() ) {
		return;
	}
	echo '<p><label for="gsley_2fa_code">Authentication code <span style="font-weight:400">(if two-factor is on)</span></label>'
		. '<input type="text" name="gsley_2fa_code" id="gsley_2fa_code" class="input" value="" size="20" autocomplete="one-time-code" inputmode="numeric" spellcheck="false"></p>';
}

// After the password check (20) and before the lock check (99).
add_filter( 'authenticate', 'gsley_2fa_authenticate', 50, 3 );
function gsley_2fa_authenticate( $user, $username, $password ) {
	if ( ! ( $user instanceof WP_User ) || ! gsley_2fa_enabled( $user->ID ) ) {
		return $user;
	}
	$code = isset( $_POST['gsley_2fa_code'] ) ? sanitize_text_field( wp_unslash( $_POST['gsley_2fa_code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- login form.
	if ( '' === $code ) {
		return new WP_Error( 'gsley_2fa_required', '<strong>Two-factor is on for this account.</strong> Enter the 6-digit code from your authenticator app (or a backup code).' );
	}
	if ( ! gsley_2fa_check_user_code( $user->ID, $code ) ) {
		gsley_sec_log( '2fa_failed', array( 'user' => $user->user_login ) );
		return new WP_Error( 'gsley_2fa', 'Incorrect authentication code.' );
	}
	return $user;
}

/* ============================================================ ENFORCEMENT */

/** Administrators who haven't enrolled are sent to their profile until they do. */
add_action( 'admin_init', 'gsley_2fa_enforce', 1 );
function gsley_2fa_enforce() {
	if ( gsley_2fa_disabled_globally() || wp_doing_ajax() || ! is_user_logged_in() ) {
		return;
	}
	$s = gsley_sec_settings();
	if ( ! $s['require_2fa'] || ! current_user_can( 'manage_options' ) || gsley_2fa_enabled( get_current_user_id() ) ) {
		return;
	}
	global $pagenow;
	if ( in_array( $pagenow, array( 'profile.php', 'admin-post.php' ), true ) ) {
		return;
	}
	wp_safe_redirect( admin_url( 'profile.php#gsley-2fa' ) );
	exit;
}

add_action( 'admin_notices', 'gsley_2fa_notice' );
function gsley_2fa_notice() {
	global $pagenow;
	if ( 'profile.php' !== $pagenow || gsley_2fa_disabled_globally() || gsley_2fa_enabled( get_current_user_id() ) || ! current_user_can( 'manage_options' ) || ! gsley_sec_settings()['require_2fa'] ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>Set up two-factor authentication to continue.</strong> Administrator accounts on this site need it. Scroll to “Two-factor authentication” below.</p></div>';
}

/* ============================================================ PROFILE UI */

add_action( 'show_user_profile', 'gsley_2fa_profile_section' );
function gsley_2fa_profile_section( $user ) {
	if ( gsley_2fa_disabled_globally() ) {
		echo '<h2 id="gsley-2fa">Two-factor authentication</h2><p>Two-factor is switched off for this site in wp-config.php (GSLEY_SECURITY_DISABLE_2FA). Remove that line to turn it back on.</p>';
		return;
	}
	$uid = $user->ID;
	echo '<h2 id="gsley-2fa">Two-factor authentication</h2><table class="form-table" role="presentation"><tbody><tr><th scope="row">Status</th><td>';

	$fresh = get_transient( 'gsley_2fa_codes_' . $uid );
	if ( $fresh ) {
		delete_transient( 'gsley_2fa_codes_' . $uid );
		echo '<div class="notice notice-success inline"><p><strong>Two-factor is on.</strong> Save these backup codes somewhere safe (each works once, if you lose your phone). They will not be shown again:</p>'
			. '<pre style="font-size:15px;line-height:1.7;background:#fff;padding:10px 14px;display:inline-block">' . esc_html( implode( "\n", (array) $fresh ) ) . '</pre></div>';
	}

	if ( gsley_2fa_enabled( $uid ) ) {
		$left = count( array_filter( (array) get_user_meta( $uid, '_gsley_2fa_backup', true ) ) );
		echo '<p><strong style="color:#15803D">On.</strong> ' . (int) $left . ' backup codes left.</p>';
		echo '<p><label for="gsley_2fa_disable_code">To turn it off or make new backup codes, enter a current code:</label><br>'
			. '<input type="text" id="gsley_2fa_disable_code" name="gsley_2fa_disable_code" autocomplete="one-time-code" inputmode="numeric" class="regular-text" style="max-width:12em"></p>'
			. '<p><label><input type="radio" name="gsley_2fa_action" value="regen"> Make new backup codes</label><br>'
			. '<label><input type="radio" name="gsley_2fa_action" value="disable"> Turn two-factor off</label></p>';
	} else {
		$pending = get_user_meta( $uid, '_gsley_2fa_pending', true );
		$secret  = $pending ? gsley_2fa_decrypt( $pending ) : '';
		if ( '' === $secret ) {
			$secret = gsley_2fa_base32_encode( random_bytes( 20 ) );
			update_user_meta( $uid, '_gsley_2fa_pending', gsley_2fa_encrypt( $secret ) );
		}
		$issuer = wp_parse_url( home_url(), PHP_URL_HOST );
		$uri    = 'otpauth://totp/' . rawurlencode( $issuer . ':' . $user->user_login ) . '?secret=' . $secret . '&issuer=' . rawurlencode( $issuer ) . '&digits=6&period=30';
		echo '<p><strong style="color:#B42318">Off.</strong></p>'
			. '<ol style="margin-left:1.2em"><li>In your authenticator app, choose <em>Add account → Enter a setup key</em> (time-based).</li>'
			. '<li>Account: <code>' . esc_html( $issuer . ':' . $user->user_login ) . '</code><br>Key: <code style="font-size:15px;letter-spacing:.06em">' . esc_html( trim( chunk_split( $secret, 4, ' ' ) ) ) . '</code></li>'
			. '<li>Enter the 6-digit code the app shows, then click <em>Update Profile</em>.</li></ol>'
			. '<p><label for="gsley_2fa_enable_code">Code from the app</label><br><input type="text" id="gsley_2fa_enable_code" name="gsley_2fa_enable_code" autocomplete="one-time-code" inputmode="numeric" class="regular-text" style="max-width:12em"></p>'
			. '<p class="description">Some apps can import this link instead: <code style="word-break:break-all">' . esc_html( $uri ) . '</code></p>';
	}
	echo '</td></tr></tbody></table>';
}

add_action( 'personal_options_update', 'gsley_2fa_profile_save' );
function gsley_2fa_profile_save( $user_id ) {
	// Core has verified the "update-user_{$user_id}" nonce before this action runs.
	if ( gsley_2fa_disabled_globally() || get_current_user_id() !== (int) $user_id ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST['gsley_2fa_enable_code'] ) && ! gsley_2fa_enabled( $user_id ) ) {
		$secret = gsley_2fa_decrypt( get_user_meta( $user_id, '_gsley_2fa_pending', true ) );
		$step   = $secret ? gsley_2fa_verify( $secret, sanitize_text_field( wp_unslash( $_POST['gsley_2fa_enable_code'] ) ) ) : false;
		if ( false === $step ) {
			add_filter( 'user_profile_update_errors', function ( $errors ) {
				$errors->add( 'gsley_2fa', '<strong>Two-factor:</strong> that code didn’t match. Check the key and your phone’s clock, then try again.' );
			} );
			return;
		}
		update_user_meta( $user_id, '_gsley_2fa_secret', gsley_2fa_encrypt( $secret ) );
		update_user_meta( $user_id, '_gsley_2fa_last', $step );
		update_user_meta( $user_id, '_gsley_2fa_enabled', '1' );
		delete_user_meta( $user_id, '_gsley_2fa_pending' );
		gsley_2fa_issue_backup_codes( $user_id );
		gsley_sec_log( '2fa_enabled', array( 'user' => get_userdata( $user_id )->user_login ) );
		return;
	}
	if ( ! empty( $_POST['gsley_2fa_action'] ) && gsley_2fa_enabled( $user_id ) ) {
		$code = isset( $_POST['gsley_2fa_disable_code'] ) ? sanitize_text_field( wp_unslash( $_POST['gsley_2fa_disable_code'] ) ) : '';
		if ( ! gsley_2fa_check_user_code( $user_id, $code ) ) {
			add_filter( 'user_profile_update_errors', function ( $errors ) {
				$errors->add( 'gsley_2fa', '<strong>Two-factor:</strong> enter a current code to change two-factor settings.' );
			} );
			return;
		}
		if ( 'disable' === $_POST['gsley_2fa_action'] ) {
			foreach ( array( '_gsley_2fa_enabled', '_gsley_2fa_secret', '_gsley_2fa_backup', '_gsley_2fa_last' ) as $k ) {
				delete_user_meta( $user_id, $k );
			}
			gsley_sec_log( '2fa_disabled', array( 'user' => get_userdata( $user_id )->user_login ), true );
		} elseif ( 'regen' === $_POST['gsley_2fa_action'] ) {
			gsley_2fa_issue_backup_codes( $user_id );
		}
	}
	// phpcs:enable
}

/** Make 10 one-time backup codes, store their hashes, show them once. */
function gsley_2fa_issue_backup_codes( $user_id ) {
	$codes  = array();
	$hashes = array();
	for ( $i = 0; $i < 10; $i++ ) {
		$c        = strtolower( wp_generate_password( 10, false, false ) );
		$codes[]  = substr( $c, 0, 5 ) . '-' . substr( $c, 5 );
		$hashes[] = wp_hash_password( $c );
	}
	update_user_meta( $user_id, '_gsley_2fa_backup', $hashes );
	set_transient( 'gsley_2fa_codes_' . $user_id, $codes, 10 * MINUTE_IN_SECONDS );
}

/** An administrator can reset another user's two-factor (e.g. lost phone). */
add_action( 'edit_user_profile', 'gsley_2fa_admin_reset_field' );
function gsley_2fa_admin_reset_field( $user ) {
	if ( ! current_user_can( 'edit_users' ) || ! gsley_2fa_enabled( $user->ID ) ) {
		return;
	}
	echo '<h2>Two-factor authentication</h2><table class="form-table" role="presentation"><tr><th scope="row">Reset</th><td>'
		. '<label><input type="checkbox" name="gsley_2fa_reset" value="1"> Turn off two-factor for this user (they will be asked to set it up again)</label></td></tr></table>';
}
add_action( 'edit_user_profile_update', 'gsley_2fa_admin_reset_save' );
function gsley_2fa_admin_reset_save( $user_id ) {
	// Core has verified the "update-user_{$user_id}" nonce before this action runs.
	if ( ! current_user_can( 'edit_users' ) || empty( $_POST['gsley_2fa_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	foreach ( array( '_gsley_2fa_enabled', '_gsley_2fa_secret', '_gsley_2fa_backup', '_gsley_2fa_last', '_gsley_2fa_pending' ) as $k ) {
		delete_user_meta( $user_id, $k );
	}
	gsley_sec_log( '2fa_reset_by_admin', array( 'detail' => get_userdata( $user_id )->user_login ), true );
}
