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
			'permission_callback' => '__return_true', // Public lead form: honeypot, server-side scoring, rate limits.
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

	if ( 'audit' === $configs[ $tool ]['engine'] ) {
		return new WP_Error( 'gsley_engine', 'Use the audit endpoint for this tool.', array( 'status' => 400 ) );
	}
	if ( strlen( (string) $request->get_body() ) > 20000 ) {
		return new WP_Error( 'gsley_too_large', 'Submission too large.', array( 'status' => 413 ) );
	}

	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) {
		$body = array();
	}

	// Honeypot: silently accept (return ok) so bots don't learn, but do nothing.
	if ( ! empty( $body['company_website'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	$email = isset( $body['email'] ) ? sanitize_email( gsley_clip( $body['email'], 190 ) ) : '';
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'gsley_bad_email', 'A valid email is required.', array( 'status' => 422 ) );
	}

	// Recompute the result on the server; never email what the browser claims.
	$result = gsley_score_submission( $configs[ $tool ], $body );
	if ( is_wp_error( $result ) ) {
		return $result;
	}

	// Per IP, per recipient (stops the form being used to email someone else repeatedly), and site-wide.
	$limited = gsley_guard( 'report', $email );
	if ( $limited ) {
		return new WP_Error( 'gsley_rate_limited', 'Too many reports requested. Please try again later.', array( 'status' => 429 ) );
	}

	$submission = array(
		'tool'       => $tool,
		'email'      => $email,
		'name'       => isset( $body['name'] ) ? gsley_clean_name( $body['name'] ) : '',
		'result'     => $result,
		'ip'         => gsley_anonymize_ip( gsley_client_ip() ),
		'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? gsley_clip( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), 255 ) : '',
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
