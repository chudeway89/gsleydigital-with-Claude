<?php
/**
 * GSLEY Diagnostics — personal data: retention, export and erasure.
 *
 * - Retention: a daily job clears IP and browser strings after 30 days and
 *   deletes leads (and their tool results) older than the retention period
 *   set in GSLEY Leads → Settings (default 24 months; 0 keeps them).
 * - Rights requests: GSLEY leads and tool results are included in WordPress's
 *   Tools → Export Personal Data and Tools → Erase Personal Data, so an
 *   access or deletion request is handled from the standard screens.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================ RETENTION */

add_action( 'init', 'gsley_privacy_schedule' );
function gsley_privacy_schedule() {
	if ( ! wp_next_scheduled( 'gsley_privacy_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'gsley_privacy_purge' );
	}
}

add_action( 'gsley_privacy_purge', 'gsley_privacy_run_purge' );
function gsley_privacy_run_purge() {
	global $wpdb;
	$leads = gsley_table_leads();
	$subs  = gsley_table_submissions();

	// Network identifiers are only needed for abuse handling: clear them after 30 days.
	$wpdb->query( $wpdb->prepare( "UPDATE $leads SET ip = '', user_agent = '' WHERE created < %s AND ( ip <> '' OR user_agent <> '' )", gmdate( 'Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB

	$months = (int) get_option( 'gsley_retention_months', 24 );
	if ( $months > 0 ) {
		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $months . ' months' ) );
		$wpdb->query( $wpdb->prepare( "DELETE s FROM $subs s JOIN $leads l ON s.lead_id = l.id WHERE l.created < %s", $cutoff ) ); // phpcs:ignore WordPress.DB
		$wpdb->query( $wpdb->prepare( "DELETE FROM $subs WHERE lead_id = 0 AND created < %s", $cutoff ) ); // phpcs:ignore WordPress.DB
		$wpdb->query( $wpdb->prepare( "DELETE FROM $leads WHERE created < %s", $cutoff ) ); // phpcs:ignore WordPress.DB
	}
}

add_action( 'admin_init', 'gsley_privacy_register_setting' );
function gsley_privacy_register_setting() {
	register_setting(
		'gsley_diagnostics_settings',
		'gsley_retention_months',
		array(
			'type'              => 'integer',
			'default'           => 24,
			'sanitize_callback' => function ( $v ) {
				return max( 0, min( 120, (int) $v ) );
			},
		)
	);
}

/* ============================================================ EXPORT / ERASE */

add_filter( 'wp_privacy_personal_data_exporters', 'gsley_privacy_register_exporter' );
function gsley_privacy_register_exporter( $exporters ) {
	$exporters['gsley-leads'] = array(
		'exporter_friendly_name' => 'GSLEY leads and diagnostic results',
		'callback'               => 'gsley_privacy_export',
	);
	return $exporters;
}

function gsley_privacy_export( $email, $page = 1 ) {
	global $wpdb;
	$leads = gsley_table_leads();
	$subs  = gsley_table_submissions();
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $leads WHERE email = %s ORDER BY id", $email ) ); // phpcs:ignore WordPress.DB
	$items = array();
	foreach ( $rows as $l ) {
		$data = array();
		foreach ( array( 'created' => 'Date', 'source' => 'Source', 'name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'organisation' => 'Organisation', 'need' => 'Need', 'message' => 'Message', 'tool' => 'Tool', 'referrer' => 'Came from', 'ip' => 'IP address (truncated)' ) as $k => $label ) {
			if ( isset( $l->$k ) && '' !== (string) $l->$k ) {
				$data[] = array( 'name' => $label, 'value' => (string) $l->$k );
			}
		}
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT tool, created, overall, band, result FROM $subs WHERE lead_id = %d", $l->id ) ) as $s ) { // phpcs:ignore WordPress.DB
			$data[] = array( 'name' => 'Diagnostic result (' . $s->tool . ', ' . $s->created . ')', 'value' => (string) $s->result );
		}
		$items[] = array(
			'group_id'    => 'gsley-leads',
			'group_label' => 'GSLEY enquiries and diagnostics',
			'item_id'     => 'gsley-lead-' . $l->id,
			'data'        => $data,
		);
	}
	return array( 'data' => $items, 'done' => true );
}

add_filter( 'wp_privacy_personal_data_erasers', 'gsley_privacy_register_eraser' );
function gsley_privacy_register_eraser( $erasers ) {
	$erasers['gsley-leads'] = array(
		'eraser_friendly_name' => 'GSLEY leads and diagnostic results',
		'callback'             => 'gsley_privacy_erase',
	);
	return $erasers;
}

function gsley_privacy_erase( $email, $page = 1 ) {
	global $wpdb;
	$leads = gsley_table_leads();
	$subs  = gsley_table_submissions();
	$ids   = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $leads WHERE email = %s", $email ) ); // phpcs:ignore WordPress.DB
	foreach ( $ids as $id ) {
		$wpdb->delete( $subs, array( 'lead_id' => (int) $id ) ); // phpcs:ignore WordPress.DB
		$wpdb->delete( $leads, array( 'id' => (int) $id ) ); // phpcs:ignore WordPress.DB
	}
	return array(
		'items_removed'  => count( $ids ),
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/* ============================================================ PRIVACY POLICY TEXT */

add_action( 'admin_init', 'gsley_privacy_policy_text' );
function gsley_privacy_policy_text() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}
	wp_add_privacy_policy_content(
		'GSLEY Diagnostics',
		'<p>When you send a brief or ask for a diagnostic report we store your name, email, phone (if given), organisation, message, the tool answers and result, the page you came from, and a truncated IP address. IP addresses and browser strings are cleared after 30 days. Records are deleted after the retention period set by the site owner. Emails are delivered by Resend; card payments for paid audits are handled by Paystack.</p>'
	);
}
