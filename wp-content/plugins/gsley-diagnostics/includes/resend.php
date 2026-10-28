<?php
/**
 * GSLEY Diagnostics — Resend client.
 *
 * All transactional email goes through Resend (not wp_mail), per the brief.
 * The API key may be set in wp-config (constant GSLEY_RESEND_API_KEY) or on the
 * settings page. Returns a normalised result so callers can log delivery state.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gsley_resend_api_key() {
	if ( defined( 'GSLEY_RESEND_API_KEY' ) && GSLEY_RESEND_API_KEY ) {
		return GSLEY_RESEND_API_KEY;
	}
	return trim( (string) get_option( 'gsley_resend_api_key', '' ) );
}

function gsley_resend_from() {
	return get_option( 'gsley_resend_from', 'GSLEY Digital <brief@gsleydigital.com>' );
}

function gsley_notify_email() {
	return get_option( 'gsley_notify_email', 'brief@gsleydigital.com' );
}

/**
 * Send an email via Resend.
 *
 * @param string|array $to      Recipient(s).
 * @param string       $subject Subject.
 * @param string       $html    HTML body.
 * @param array        $args    Optional: reply_to, text.
 * @return array { ok:bool, id:string, error:string }
 */
function gsley_resend_send( $to, $subject, $html, $args = array() ) {
	$key = gsley_resend_api_key();
	if ( ! $key ) {
		return array( 'ok' => false, 'id' => '', 'error' => 'Resend API key not configured.' );
	}

	$body = array(
		'from'    => gsley_resend_from(),
		'to'      => is_array( $to ) ? $to : array( $to ),
		'subject' => $subject,
		'html'    => $html,
	);
	if ( ! empty( $args['reply_to'] ) ) {
		$body['reply_to'] = $args['reply_to'];
	}
	if ( ! empty( $args['text'] ) ) {
		$body['text'] = $args['text'];
	}

	$response = wp_remote_post(
		'https://api.resend.com/emails',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'id' => '', 'error' => $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( $code >= 200 && $code < 300 && ! empty( $data['id'] ) ) {
		return array( 'ok' => true, 'id' => $data['id'], 'error' => '' );
	}

	$error = is_array( $data ) && ! empty( $data['message'] ) ? $data['message'] : ( 'Resend HTTP ' . $code );
	return array( 'ok' => false, 'id' => '', 'error' => $error );
}
