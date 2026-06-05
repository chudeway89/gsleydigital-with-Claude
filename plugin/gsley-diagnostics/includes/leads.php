<?php
/**
 * GSLEY Diagnostics — lead + submission persistence helpers.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert a lead (one row per capture event).
 *
 * @param array $data
 * @return int Lead id (0 on failure).
 */
function gsley_insert_lead( $data ) {
	global $wpdb;
	$row = wp_parse_args(
		$data,
		array(
			'created'      => current_time( 'mysql' ),
			'source'       => 'contact',
			'name'         => '',
			'email'        => '',
			'organisation' => '',
			'need'         => '',
			'message'      => '',
			'tool'         => '',
			'ip'           => '',
			'user_agent'   => '',
		)
	);
	$ok = $wpdb->insert( gsley_table_leads(), $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return $ok ? (int) $wpdb->insert_id : 0;
}

/**
 * Record the delivery outcome of a lead-notification email.
 */
function gsley_update_lead_delivery( $lead_id, $result ) {
	global $wpdb;
	$wpdb->update(
		gsley_table_leads(),
		array(
			'delivery_status' => $result['ok'] ? 'sent' : 'failed',
			'delivery_id'     => $result['id'],
			'delivery_error'  => $result['error'],
		),
		array( 'id' => (int) $lead_id )
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * Update a lead's follow-up status and/or notes.
 */
function gsley_update_lead_status( $lead_id, $status, $notes ) {
	global $wpdb;
	$allowed = array( 'new', 'contacted', 'qualified', 'won', 'lost' );
	if ( ! in_array( $status, $allowed, true ) ) {
		$status = 'new';
	}
	return $wpdb->update(
		gsley_table_leads(),
		array( 'status' => $status, 'notes' => $notes ),
		array( 'id' => (int) $lead_id )
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * Insert a tool submission row.
 *
 * @return int Submission id.
 */
function gsley_insert_submission( $data ) {
	global $wpdb;
	$row = wp_parse_args(
		$data,
		array(
			'lead_id'         => 0,
			'created'         => current_time( 'mysql' ),
			'tool'            => '',
			'overall'         => null,
			'band'            => '',
			'priority'        => '',
			'result'          => '',
			'delivery_status' => 'pending',
		)
	);
	$ok = $wpdb->insert( gsley_table_submissions(), $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return $ok ? (int) $wpdb->insert_id : 0;
}

/**
 * Record the delivery outcome of a tool report email.
 */
function gsley_update_submission_delivery( $submission_id, $result ) {
	global $wpdb;
	$wpdb->update(
		gsley_table_submissions(),
		array(
			'delivery_status' => $result['ok'] ? 'sent' : 'failed',
			'delivery_id'     => $result['id'],
			'delivery_error'  => $result['error'],
			'delivered_at'    => $result['ok'] ? current_time( 'mysql' ) : null,
		),
		array( 'id' => (int) $submission_id )
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
