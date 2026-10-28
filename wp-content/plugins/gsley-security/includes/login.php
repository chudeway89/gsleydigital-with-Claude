<?php
/**
 * GSLEY Security — authentication surface.
 *
 * - Login throttling: 5 failures for one username from one IP in 15 minutes
 *   locks that pair for 30 minutes; 20 failures from one IP in an hour locks
 *   the IP for an hour. Successful logins clear the counters.
 * - One generic error for bad username or password (no account probing).
 * - User enumeration closed: /wp-json/wp/v2/users, ?author=N, author
 *   archives, oEmbed author fields, and the users sitemap.
 * - XML-RPC and application passwords off (both bypass the login form and 2FA).
 * - WordPress version hidden; auth cookies get SameSite=Lax; administrator
 *   sessions are shorter.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================ THROTTLING */

function gsley_sec_lock_key( $kind, $id ) {
	return 'gsley_sec_' . $kind . '_' . substr( md5( strtolower( (string) $id ) ), 0, 24 );
}

/** Seconds left on a lock, or 0. */
function gsley_sec_locked( $username ) {
	$ip = gsley_sec_ip();
	foreach ( array( gsley_sec_lock_key( 'lockpair', $ip . '|' . $username ), gsley_sec_lock_key( 'lockip', $ip ) ) as $k ) {
		$until = (int) get_transient( $k );
		if ( $until > time() ) {
			return $until - time();
		}
	}
	return 0;
}

// Runs last, so a correct password can't get past an active lock.
add_filter( 'authenticate', 'gsley_sec_block_locked', 99, 2 );
function gsley_sec_block_locked( $user, $username ) {
	if ( '' === (string) $username ) {
		return $user;
	}
	$left = gsley_sec_locked( $username );
	if ( $left ) {
		return new WP_Error( 'gsley_locked', sprintf( 'Too many failed attempts. Try again in %d minutes.', (int) ceil( $left / 60 ) ) );
	}
	return $user;
}

add_action( 'wp_login_failed', 'gsley_sec_login_failed', 10, 2 );
function gsley_sec_login_failed( $username, $error = null ) {
	if ( $error instanceof WP_Error && 'gsley_locked' === $error->get_error_code() ) {
		return; // already locked; don't extend
	}
	$ip   = gsley_sec_ip();
	$pair = gsley_sec_lock_key( 'failpair', $ip . '|' . $username );
	$ipk  = gsley_sec_lock_key( 'failip', $ip );
	$np   = (int) get_transient( $pair ) + 1;
	$ni   = (int) get_transient( $ipk ) + 1;
	set_transient( $pair, $np, 15 * MINUTE_IN_SECONDS );
	set_transient( $ipk, $ni, HOUR_IN_SECONDS );
	gsley_sec_log( 'login_failed', array( 'user' => sanitize_user( $username ), 'detail' => $error instanceof WP_Error ? $error->get_error_code() : '' ) );

	if ( $np >= 5 ) {
		set_transient( gsley_sec_lock_key( 'lockpair', $ip . '|' . $username ), time() + 30 * MINUTE_IN_SECONDS, 30 * MINUTE_IN_SECONDS );
		delete_transient( $pair );
		gsley_sec_log( 'lockout', array( 'user' => sanitize_user( $username ), 'detail' => '5 failures in 15 min' ), true );
	}
	if ( $ni >= 20 ) {
		set_transient( gsley_sec_lock_key( 'lockip', $ip ), time() + HOUR_IN_SECONDS, HOUR_IN_SECONDS );
		delete_transient( $ipk );
		gsley_sec_log( 'lockout_ip', array( 'detail' => '20 failures in 1 hour' ), true );
	}
}

add_action( 'wp_login', 'gsley_sec_login_ok', 10, 2 );
function gsley_sec_login_ok( $login, $user ) {
	$ip = gsley_sec_ip();
	delete_transient( gsley_sec_lock_key( 'failpair', $ip . '|' . $login ) );
	gsley_sec_log( 'login', array( 'user' => $login, 'detail' => implode( ',', (array) $user->roles ) ) );
}

/** One message for unknown user, wrong password, or wrong code. */
add_filter( 'login_errors', 'gsley_sec_generic_login_error' );
function gsley_sec_generic_login_error( $message ) {
	global $errors;
	if ( $errors instanceof WP_Error ) {
		$codes = $errors->get_error_codes();
		if ( array_intersect( $codes, array( 'gsley_locked', 'gsley_2fa_required', 'empty_username', 'empty_password', 'expired_session', 'expiredkey', 'invalidkey' ) ) ) {
			return $message;
		}
		if ( array_intersect( $codes, array( 'invalid_username', 'invalid_email', 'incorrect_password', 'gsley_2fa' ) ) ) {
			return '<strong>Error:</strong> The username, password, or authentication code is incorrect.';
		}
	}
	return $message;
}

/* ============================================================ ENUMERATION */

add_filter( 'rest_endpoints', 'gsley_sec_hide_user_endpoints' );
function gsley_sec_hide_user_endpoints( $endpoints ) {
	if ( current_user_can( 'list_users' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( preg_match( '#^/wp/v2/users#', $route ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}

add_action( 'template_redirect', 'gsley_sec_block_author_scans', 1 );
function gsley_sec_block_author_scans() {
	if ( is_user_logged_in() ) {
		return;
	}
	if ( isset( $_GET['author'] ) || is_author() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}

add_filter( 'oembed_response_data', 'gsley_sec_oembed_no_author' );
function gsley_sec_oembed_no_author( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
}

add_filter( 'wp_sitemaps_add_provider', 'gsley_sec_no_user_sitemap', 10, 2 );
function gsley_sec_no_user_sitemap( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}

/* ============================================================ BYPASS ROUTES OFF */

// xmlrpc.php is refused outright (even system.multicall / pingbacks).
if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	status_header( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( 'XML-RPC is disabled on this site.' );
}
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'wp_headers', 'gsley_sec_no_pingback_header' );
function gsley_sec_no_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_is_application_passwords_available', '__return_false' );

/* ============================================================ FINGERPRINTING */

remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* ============================================================ SESSIONS */

/** Administrators: 8 hours (default) without "Remember me", 3 days with it. */
add_filter( 'auth_cookie_expiration', 'gsley_sec_admin_session_length', 10, 3 );
function gsley_sec_admin_session_length( $length, $user_id, $remember ) {
	if ( user_can( $user_id, 'manage_options' ) ) {
		$hours = max( 1, (int) gsley_sec_settings()['admin_session_h'] );
		return $remember ? 3 * DAY_IN_SECONDS : $hours * HOUR_IN_SECONDS;
	}
	return $length;
}

/**
 * Add SameSite=Lax to WordPress's auth cookies. WordPress sets them with
 * setcookie() without SameSite, so the header is rewritten just before output.
 * (HttpOnly and Secure are already set by WordPress on HTTPS.)
 */
add_action( 'plugins_loaded', 'gsley_sec_register_cookie_rewrite', 1 );
function gsley_sec_register_cookie_rewrite() {
	if ( function_exists( 'header_register_callback' ) ) {
		header_register_callback( 'gsley_sec_samesite_cookies' );
	}
}
function gsley_sec_samesite_cookies() {
	$cookies = array();
	$changed = false;
	foreach ( headers_list() as $h ) {
		if ( 0 !== stripos( $h, 'Set-Cookie:' ) ) {
			continue;
		}
		$c = trim( substr( $h, 11 ) );
		if ( preg_match( '/^(wordpress_|wp-settings|wp_lang|wordpress_test_cookie)/i', $c ) && false === stripos( $c, 'samesite' ) ) {
			$c      .= '; SameSite=Lax';
			$changed = true;
		}
		$cookies[] = $c;
	}
	if ( ! $changed ) {
		return;
	}
	header_remove( 'Set-Cookie' );
	foreach ( $cookies as $c ) {
		header( 'Set-Cookie: ' . $c, false );
	}
}

/* ============================================================ ERRORS */

// Never print PHP errors to visitors in production (logging is unaffected).
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ( defined( 'WP_DEBUG_DISPLAY' ) && ! WP_DEBUG_DISPLAY ) ) {
	@ini_set( 'display_errors', '0' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.PHP.IniSet
}

/* ============================================================ ROLE CHANGES */

add_action( 'set_user_role', 'gsley_sec_role_changed', 10, 3 );
function gsley_sec_role_changed( $user_id, $role, $old_roles ) {
	if ( 'administrator' === $role && ! in_array( 'administrator', (array) $old_roles, true ) ) {
		$u = get_userdata( $user_id );
		gsley_sec_log( 'admin_granted', array( 'detail' => $u ? $u->user_login : (string) $user_id ), true );
	}
}
add_action( 'user_register', 'gsley_sec_user_registered' );
function gsley_sec_user_registered( $user_id ) {
	if ( user_can( $user_id, 'manage_options' ) ) {
		$u = get_userdata( $user_id );
		gsley_sec_log( 'admin_created', array( 'detail' => $u ? $u->user_login : (string) $user_id ), true );
	}
}
add_action( 'activated_plugin', function ( $plugin ) {
	gsley_sec_log( 'plugin_activated', array( 'detail' => $plugin ) );
} );
add_action( 'deactivated_plugin', function ( $plugin ) {
	gsley_sec_log( 'plugin_deactivated', array( 'detail' => $plugin ), false !== strpos( $plugin, 'gsley-security' ) );
} );
