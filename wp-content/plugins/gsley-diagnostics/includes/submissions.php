<?php
/**
 * GSLEY Diagnostics — tool submission handler.
 *
 * Hooks the gsley_diagnostics_submission action (fired by the REST endpoint):
 * persists the lead + result, builds and sends the report via Resend, and logs
 * delivery status against the submission row.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'gsley_diagnostics_submission', 'gsley_handle_tool_submission', 10, 2 );

/**
 * @param array  $submission Sanitised submission from the REST endpoint.
 * @param string $tool       Tool slug.
 */
function gsley_handle_tool_submission( $submission, $tool ) {
	$result = isset( $submission['result'] ) && is_array( $submission['result'] ) ? $submission['result'] : array();

	$lead_id = gsley_insert_lead(
		array(
			'source'     => 'tool',
			'name'       => isset( $submission['name'] ) ? $submission['name'] : '',
			'email'      => $submission['email'],
			'tool'       => $tool,
			'ip'         => isset( $submission['ip'] ) ? $submission['ip'] : '',
			'user_agent' => isset( $submission['user_agent'] ) ? $submission['user_agent'] : '',
			'created'    => isset( $submission['created'] ) ? $submission['created'] : current_time( 'mysql' ),
		)
	);

	$submission_id = gsley_insert_submission(
		array(
			'lead_id'  => $lead_id,
			'tool'     => $tool,
			'overall'  => isset( $result['overall'] ) ? (int) $result['overall'] : ( isset( $result['total'] ) ? (int) $result['total'] : null ),
			'band'     => isset( $result['band'] ) ? sanitize_text_field( $result['band'] ) : '',
			'priority' => isset( $result['priority'] ) ? sanitize_text_field( $result['priority'] ) : '',
			'result'   => wp_json_encode( $result ),
		)
	);

	// Build + send the report.
	$report = gsley_build_report_email( $tool, $submission );
	$send   = gsley_resend_send(
		$submission['email'],
		$report['subject'],
		$report['html'],
		array( 'reply_to' => gsley_notify_email() )
	);

	if ( $submission_id ) {
		gsley_update_submission_delivery( $submission_id, $send );
	}
}
