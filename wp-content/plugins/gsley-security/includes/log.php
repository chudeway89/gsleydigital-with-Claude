<?php
/**
 * GSLEY Security — security event log and alerts.
 *
 * Who / what / when / where for security-relevant events: logins, failures,
 * lockouts, 2FA changes, new administrators, plugin changes, secret changes,
 * lead exports, rate-limit hits, payment/webhook anomalies. No passwords,
 * codes, card data or form contents are ever logged.
 *
 * Stored in a dedicated table, kept 90 days. High-severity events email the
 * alert address (at most one email per event type per hour). Every event is
 * also written to the PHP error log, so the host's log pipeline gets a copy
 * that WordPress admins cannot edit.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GSLEY_SEC_DB_VER', '1' );

function gsley_sec_table() {
	global $wpdb;
	return $wpdb->prefix . 'gsley_security_log';
}

add_action( 'plugins_loaded', 'gsley_sec_install', 5 );
function gsley_sec_install() {
	if ( get_option( 'gsley_sec_db_ver' ) === GSLEY_SEC_DB_VER ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t = gsley_sec_table();
	dbDelta(
		"CREATE TABLE $t (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		event varchar(40) NOT NULL DEFAULT '',
		user_login varchar(60) NOT NULL DEFAULT '',
		ip varchar(64) NOT NULL DEFAULT '',
		path varchar(190) NOT NULL DEFAULT '',
		detail varchar(255) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY created (created),
		KEY event (event)
		) " . $wpdb->get_charset_collate() . ';'
	);
	update_option( 'gsley_sec_db_ver', GSLEY_SEC_DB_VER, false );
}

/**
 * Record an event.
 *
 * @param string $event  Short event code.
 * @param array  $ctx    user, detail.
 * @param bool   $alert  Email the alert address (throttled).
 */
function gsley_sec_log( $event, $ctx = array(), $alert = false ) {
	global $wpdb;
	$user = isset( $ctx['user'] ) ? (string) $ctx['user'] : '';
	if ( '' === $user && function_exists( 'wp_get_current_user' ) && is_user_logged_in() ) {
		$user = wp_get_current_user()->user_login;
	}
	$path   = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$detail = isset( $ctx['detail'] ) ? sanitize_text_field( (string) $ctx['detail'] ) : '';
	$row    = array(
		'created'    => current_time( 'mysql', true ),
		'event'      => substr( sanitize_key( $event ), 0, 40 ),
		'user_login' => substr( sanitize_user( $user ), 0, 60 ),
		'ip'         => gsley_sec_ip(),
		'path'       => substr( $path, 0, 190 ),
		'detail'     => substr( $detail, 0, 255 ),
	);
	if ( get_option( 'gsley_sec_db_ver' ) === GSLEY_SEC_DB_VER ) {
		$wpdb->insert( gsley_sec_table(), $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
	// A copy outside the database, in the host's error log.
	error_log( 'GSLEY-SECURITY ' . wp_json_encode( $row ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

	if ( $alert ) {
		gsley_sec_alert( $row );
	}
}

/** Events from the diagnostics plugin (rate limits, payment/webhook anomalies, exports). */
add_action( 'gsley_security_event', 'gsley_sec_external_event', 10, 2 );
function gsley_sec_external_event( $event, $ctx ) {
	$ctx    = is_array( $ctx ) ? $ctx : array();
	$detail = array();
	foreach ( $ctx as $k => $v ) {
		if ( is_scalar( $v ) ) {
			$detail[] = $k . '=' . $v;
		}
	}
	$alert = in_array( $event, array( 'webhook_bad_signature', 'payment_mismatch', 'secret_changed', 'leads_exported' ), true );
	gsley_sec_log( $event, array( 'detail' => implode( ' ', $detail ) ), $alert );
}

/** Email an alert, at most once per event type per hour. */
function gsley_sec_alert( $row ) {
	$key = 'gsley_sec_alerted_' . $row['event'];
	if ( get_transient( $key ) ) {
		return;
	}
	set_transient( $key, 1, HOUR_IN_SECONDS );
	$s  = gsley_sec_settings();
	$to = is_email( $s['alert_email'] ) ? $s['alert_email'] : get_option( 'admin_email' );
	if ( ! is_email( $to ) ) {
		return;
	}
	$subject = '[' . wp_parse_url( home_url(), PHP_URL_HOST ) . '] Security alert: ' . $row['event'];
	$body    = "A security event was recorded on your site.\n\n"
		. 'Event: ' . $row['event'] . "\n"
		. 'When (UTC): ' . $row['created'] . "\n"
		. 'User: ' . ( $row['user_login'] ? $row['user_login'] : '—' ) . "\n"
		. 'IP: ' . $row['ip'] . "\n"
		. 'Path: ' . $row['path'] . "\n"
		. 'Detail: ' . $row['detail'] . "\n\n"
		. 'Review: ' . admin_url( 'options-general.php?page=gsley-security' ) . "\n"
		. "If this wasn't you or your team, follow the incident steps in SECURITY.md.\n";
	if ( function_exists( 'gsley_resend_send' ) && function_exists( 'gsley_resend_api_key' ) && gsley_resend_api_key() ) {
		gsley_resend_send( $to, $subject, '<pre style="font-family:monospace;font-size:13px">' . esc_html( $body ) . '</pre>' );
	} else {
		wp_mail( $to, $subject, $body );
	}
}

/* ============================================================ RETENTION */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'gsley_sec_prune' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'gsley_sec_prune' );
	}
} );
add_action( 'gsley_sec_prune', 'gsley_sec_prune_log' );
function gsley_sec_prune_log() {
	global $wpdb;
	$t = gsley_sec_table();
	$wpdb->query( $wpdb->prepare( "DELETE FROM $t WHERE created < %s", gmdate( 'Y-m-d H:i:s', time() - 90 * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
}
