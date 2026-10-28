<?php
/**
 * Plugin Name:       GSLEY Security
 * Plugin URI:        https://gsleydigital.com/
 * Description:       Site-wide hardening for gsleydigital.com: security headers and CSP, login throttling, two-factor authentication (TOTP) for administrators, breached-password checks, user-enumeration and XML-RPC lockdown, shorter admin sessions, and a security event log with email alerts. Settings → GSLEY Security.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            GSLEY Digital
 * License:           GPL-2.0-or-later
 * Text Domain:       gsley-security
 *
 * Emergency off-switch: add `define( 'GSLEY_SECURITY_DISABLE_2FA', true );` to
 * wp-config.php to bypass two-factor (e.g. lost phone and backup codes), or
 * rename this plugin's folder over SFTP to disable everything.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GSLEY_SECURITY_VER', '1.0.0' );
define( 'GSLEY_SECURITY_DIR', plugin_dir_path( __FILE__ ) );

// No theme/plugin code editor in WP-admin: a stolen admin session can't become code execution.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/** Settings with defaults. */
function gsley_sec_settings() {
	$defaults = array(
		'csp_enforce'      => 0,   // full CSP: 0 = report-only, 1 = enforce
		'csp_extra'        => '',  // extra allowed hosts, one per line
		'hsts'             => 1,
		'hsts_subdomains'  => 0,
		'hsts_preload'     => 0,
		'require_2fa'      => 1,   // administrators must enrol
		'alert_email'      => '',  // defaults to the site admin email
		'admin_session_h'  => 8,   // admin login lifetime (hours) without "remember me"
	);
	$saved = get_option( 'gsley_security', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

/** The request IP (EasyWP sets REMOTE_ADDR to the visitor). */
function gsley_sec_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

require_once GSLEY_SECURITY_DIR . 'includes/log.php';
require_once GSLEY_SECURITY_DIR . 'includes/headers.php';
require_once GSLEY_SECURITY_DIR . 'includes/login.php';
require_once GSLEY_SECURITY_DIR . 'includes/passwords.php';
require_once GSLEY_SECURITY_DIR . 'includes/two-factor.php';
if ( is_admin() ) {
	require_once GSLEY_SECURITY_DIR . 'includes/admin.php';
}
