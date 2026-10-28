<?php
/**
 * GSLEY Security — password policy.
 *
 * WordPress hashes passwords itself (bcrypt from WordPress 6.8; keep core
 * updated). On top of that, any new password set through the profile screen
 * or a reset must be at least 12 characters and must not appear in a known
 * data breach. The breach check uses Have I Been Pwned's k-anonymity range
 * API: only the first 5 characters of the password's SHA-1 hash leave the
 * server, never the password. If the service is unreachable the check is
 * skipped rather than blocking the change.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Problems with a candidate password, as a message, or ''. */
function gsley_sec_password_problem( $password ) {
	$password = (string) $password;
	if ( '' === $password ) {
		return '';
	}
	if ( strlen( $password ) < 12 ) {
		return 'Use a password of at least 12 characters.';
	}
	if ( gsley_sec_password_breached( $password ) ) {
		return 'This password has appeared in a known data breach. Choose a different one.';
	}
	return '';
}

/** True if the password appears in the Have I Been Pwned corpus. */
function gsley_sec_password_breached( $password ) {
	$hash   = strtoupper( sha1( $password ) );
	$prefix = substr( $hash, 0, 5 );
	$suffix = substr( $hash, 5 );
	$r      = wp_remote_get(
		'https://api.pwnedpasswords.com/range/' . $prefix,
		array(
			'timeout'    => 5,
			'headers'    => array( 'Add-Padding' => 'true' ),
			'user-agent' => 'GSLEY-Security/1.0',
		)
	);
	if ( is_wp_error( $r ) || 200 !== (int) wp_remote_retrieve_response_code( $r ) ) {
		return false; // fail open: never block a password change on a network error
	}
	foreach ( preg_split( '/\r?\n/', (string) wp_remote_retrieve_body( $r ) ) as $line ) {
		$parts = explode( ':', trim( $line ) );
		if ( 2 === count( $parts ) && hash_equals( $suffix, strtoupper( $parts[0] ) ) && (int) $parts[1] > 0 ) {
			return true;
		}
	}
	return false;
}

/** Profile screen (own profile and editing another user). */
add_action( 'user_profile_update_errors', 'gsley_sec_check_profile_password', 10, 3 );
function gsley_sec_check_profile_password( $errors, $update, $user ) {
	$pass = isset( $_POST['pass1'] ) ? (string) wp_unslash( $_POST['pass1'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- core verifies the nonce; passwords are not sanitised.
	$msg  = gsley_sec_password_problem( $pass );
	if ( $msg ) {
		$errors->add( 'gsley_weak_password', '<strong>Error:</strong> ' . esc_html( $msg ) );
	}
}

/** Lost-password reset form. */
add_action( 'validate_password_reset', 'gsley_sec_check_reset_password', 10, 2 );
function gsley_sec_check_reset_password( $errors, $user ) {
	$pass = isset( $_POST['pass1'] ) ? (string) wp_unslash( $_POST['pass1'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	$msg  = gsley_sec_password_problem( $pass );
	if ( $msg ) {
		$errors->add( 'gsley_weak_password', esc_html( $msg ) );
	}
}
