<?php
/**
 * GSLEY Security — HTTP response headers.
 *
 * Always enforced on the front end: nosniff, frame protection, referrer policy,
 * permissions policy, COOP, HSTS (HTTPS only), and a baseline CSP that cannot
 * break page content (frame-ancestors, base-uri, object-src, form-action).
 *
 * The full CSP (script/style/img/connect/frame sources) ships in Report-Only
 * mode so it can be checked against the live plugins (Site Kit, WooCommerce,
 * TranslatePress) before it is enforced from Settings → GSLEY Security.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'send_headers', 'gsley_sec_front_headers' );
function gsley_sec_front_headers() {
	if ( headers_sent() ) {
		return;
	}
	gsley_sec_common_headers();
	$s = gsley_sec_settings();

	header( "Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'" . ( is_ssl() ? '; upgrade-insecure-requests' : '' ) );
	header( ( $s['csp_enforce'] ? 'Content-Security-Policy: ' : 'Content-Security-Policy-Report-Only: ' ) . gsley_sec_full_csp() );
}

// wp-admin and wp-login.php get the non-CSP headers (the editor needs its own inline/blob sources).
add_action( 'admin_init', 'gsley_sec_common_headers' );
add_action( 'login_init', 'gsley_sec_common_headers' );

function gsley_sec_common_headers() {
	if ( headers_sent() ) {
		return;
	}
	$s = gsley_sec_settings();
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), usb=(), payment=(), interest-cohort=()' );
	header( 'Cross-Origin-Opener-Policy: same-origin-allow-popups' );
	if ( $s['hsts'] && is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' . ( $s['hsts_subdomains'] ? '; includeSubDomains' : '' ) . ( $s['hsts_subdomains'] && $s['hsts_preload'] ? '; preload' : '' ) );
	}
	header_remove( 'X-Powered-By' );
}

/** The full Content-Security-Policy for public pages. */
function gsley_sec_full_csp() {
	$s     = gsley_sec_settings();
	$extra = array();
	foreach ( preg_split( '/\s+/', (string) $s['csp_extra'] ) as $h ) {
		$h = trim( $h );
		if ( $h && preg_match( '#^(https://)?[a-z0-9*.-]+(\.[a-z]{2,})(:\d+)?$#i', $h ) ) {
			$extra[] = 0 === strpos( $h, 'https://' ) ? $h : 'https://' . $h;
		}
	}
	// The booking scheduler (GSLEY Settings) is embedded on Contact.
	$frames = array( "'self'", 'https://cal.com', 'https://*.cal.com', 'https://calendly.com', 'https://*.calendly.com', 'https://www.youtube-nocookie.com', 'https://www.youtube.com' );
	if ( function_exists( 'gsley_site' ) && gsley_site( 'booking_url' ) ) {
		$host = wp_parse_url( gsley_site( 'booking_url' ), PHP_URL_HOST );
		if ( $host ) {
			$frames[] = 'https://' . $host;
		}
	}
	$google = 'https://www.googletagmanager.com https://*.googletagmanager.com https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://stats.g.doubleclick.net';
	$x      = $extra ? ' ' . implode( ' ', array_unique( $extra ) ) : '';
	$policy = array(
		"default-src 'self'",
		"script-src 'self' 'unsafe-inline' " . $google . $x, // WordPress core and plugins print inline scripts
		"style-src 'self' 'unsafe-inline'" . $x,
		"img-src 'self' data: https:",
		"font-src 'self' data:" . $x,
		"connect-src 'self' " . $google . $x,
		'frame-src ' . implode( ' ', array_unique( $frames ) ) . $x,
		"frame-ancestors 'self'",
		"base-uri 'self'",
		"object-src 'none'",
		"form-action 'self'",
		"worker-src 'self' blob:",
	);
	return implode( '; ', apply_filters( 'gsley_security_csp', $policy ) );
}

/**
 * /.well-known/security.txt (RFC 9116): where to report a vulnerability.
 * Contact comes from GSLEY Settings (email) or the site admin email.
 */
add_action( 'init', 'gsley_sec_security_txt', 0 );
function gsley_sec_security_txt() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	if ( '/.well-known/security.txt' !== $path && '/security.txt' !== $path ) {
		return;
	}
	$email = function_exists( 'gsley_site' ) && is_email( gsley_site( 'email' ) ) ? gsley_site( 'email' ) : get_option( 'admin_email' );
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'Contact: mailto:' . esc_html( $email ) . "\n"
		. 'Expires: ' . esc_html( gmdate( 'Y-m-d\T00:00:00\Z', strtotime( '+1 year' ) ) ) . "\n"
		. 'Preferred-Languages: en' . "\n"
		. 'Canonical: ' . esc_url( home_url( '/.well-known/security.txt' ) ) . "\n";
	exit;
}
