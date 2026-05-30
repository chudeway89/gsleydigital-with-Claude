<?php
/**
 * GSLEY Diagnostics — submission endpoint.
 *
 * Receives the answer-first result + email on the report step. This task wires
 * the contract and validation; persistence + Resend delivery are added by the
 * backend task via the `gsley_diagnostics_submission` action — keeping the
 * tools self-contained while the data layer is built separately.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'gsley_diagnostics_register_routes' );

function gsley_diagnostics_register_routes() {
	register_rest_route(
		'gsley/v1',
		'/tools/(?P<id>[a-z0-9-]+)',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true', // Public lead form; protected by honeypot + validation.
			'callback'            => 'gsley_diagnostics_handle_submission',
			'args'                => array(
				'id' => array( 'sanitize_callback' => 'sanitize_key' ),
			),
		)
	);
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function gsley_diagnostics_handle_submission( $request ) {
	$tool    = sanitize_key( $request['id'] );
	$configs = gsley_diagnostics_configs();
	if ( ! isset( $configs[ $tool ] ) ) {
		return new WP_Error( 'gsley_unknown_tool', 'Unknown tool.', array( 'status' => 404 ) );
	}

	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) {
		$body = array();
	}

	// Honeypot: silently accept (return ok) so bots don't learn, but do nothing.
	if ( ! empty( $body['company_website'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	$email = isset( $body['email'] ) ? sanitize_email( $body['email'] ) : '';
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'gsley_bad_email', 'A valid email is required.', array( 'status' => 422 ) );
	}

	// Lightweight rate limit per IP (10 / 10 min) — defence in depth.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'gsley_diag_rl_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 10 ) {
		return new WP_Error( 'gsley_rate_limited', 'Too many submissions. Please try again shortly.', array( 'status' => 429 ) );
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$submission = array(
		'tool'       => $tool,
		'email'      => $email,
		'name'       => isset( $body['name'] ) ? sanitize_text_field( $body['name'] ) : '',
		'result'     => $body, // full payload (answers/inputs/selections, score, priority…)
		'ip'         => $ip,
		'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'created'    => current_time( 'mysql' ),
	);

	/**
	 * Persist the lead + tool result and deliver the report via Resend.
	 *
	 * The backend task hooks this to write to gsley_leads / gsley_tool_submissions
	 * and call Resend. Until then the endpoint validates and acknowledges so the
	 * answer-first flow works end to end.
	 *
	 * @param array  $submission Sanitised submission.
	 * @param string $tool       Tool slug.
	 */
	do_action( 'gsley_diagnostics_submission', $submission, $tool );

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}
