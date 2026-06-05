<?php
/**
 * GSLEY Diagnostics — Paystack client (online payments for the paid audits).
 *
 * Redirect flow: initialize a transaction server-side, send the buyer to the
 * returned authorization_url, then verify by reference (and via webhook) before
 * unlocking. Keys come from constants (preferred) or the settings page.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gsley_paystack_secret() {
	if ( defined( 'GSLEY_PAYSTACK_SECRET_KEY' ) && GSLEY_PAYSTACK_SECRET_KEY ) {
		return GSLEY_PAYSTACK_SECRET_KEY;
	}
	return trim( (string) get_option( 'gsley_paystack_secret', '' ) );
}

function gsley_paystack_public() {
	if ( defined( 'GSLEY_PAYSTACK_PUBLIC_KEY' ) && GSLEY_PAYSTACK_PUBLIC_KEY ) {
		return GSLEY_PAYSTACK_PUBLIC_KEY;
	}
	return trim( (string) get_option( 'gsley_paystack_public', '' ) );
}

function gsley_paystack_currency() {
	return strtoupper( (string) get_option( 'gsley_paystack_currency', 'USD' ) );
}

function gsley_paystack_ready() {
	return '' !== gsley_paystack_secret();
}

/**
 * Initialize a transaction.
 *
 * @param string $email        Buyer email.
 * @param float  $amount_major Amount in the main unit (e.g. 10 for $10).
 * @param string $reference    Unique reference.
 * @param string $callback_url Where Paystack returns the buyer.
 * @param array  $metadata     Extra metadata.
 * @return array { ok, authorization_url, reference, error }
 */
function gsley_paystack_init( $email, $amount_major, $reference, $callback_url, $metadata = array() ) {
	$secret = gsley_paystack_secret();
	if ( ! $secret ) {
		return array( 'ok' => false, 'authorization_url' => '', 'reference' => '', 'error' => 'Payments are not configured yet.' );
	}
	$body = array(
		'email'        => $email,
		'amount'       => (int) round( (float) $amount_major * 100 ), // subunit (cents/kobo)
		'currency'     => gsley_paystack_currency(),
		'reference'    => $reference,
		'callback_url' => $callback_url,
		'metadata'     => $metadata,
	);
	$response = wp_remote_post(
		'https://api.paystack.co/transaction/initialize',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $secret,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
			'timeout' => 20,
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'authorization_url' => '', 'reference' => '', 'error' => $response->get_error_message() );
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! empty( $data['status'] ) && ! empty( $data['data']['authorization_url'] ) ) {
		return array(
			'ok'                => true,
			'authorization_url' => $data['data']['authorization_url'],
			'reference'         => isset( $data['data']['reference'] ) ? $data['data']['reference'] : $reference,
			'error'            => '',
		);
	}
	$err = is_array( $data ) && ! empty( $data['message'] ) ? $data['message'] : 'Could not start the payment.';
	return array( 'ok' => false, 'authorization_url' => '', 'reference' => '', 'error' => $err );
}

/**
 * Verify a transaction by reference (authoritative).
 *
 * @return array { ok, success, amount_major, currency, email, error }
 */
function gsley_paystack_verify( $reference ) {
	$secret = gsley_paystack_secret();
	if ( ! $secret ) {
		return array( 'ok' => false, 'success' => false, 'error' => 'Payments are not configured.' );
	}
	$response = wp_remote_get(
		'https://api.paystack.co/transaction/verify/' . rawurlencode( $reference ),
		array(
			'headers' => array( 'Authorization' => 'Bearer ' . $secret ),
			'timeout' => 20,
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'success' => false, 'error' => $response->get_error_message() );
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $data['status'] ) || empty( $data['data'] ) ) {
		$err = is_array( $data ) && ! empty( $data['message'] ) ? $data['message'] : 'Could not verify the payment.';
		return array( 'ok' => false, 'success' => false, 'error' => $err );
	}
	$d = $data['data'];
	return array(
		'ok'           => true,
		'success'      => isset( $d['status'] ) && 'success' === $d['status'],
		'amount_major' => isset( $d['amount'] ) ? ( (int) $d['amount'] ) / 100 : 0,
		'currency'     => isset( $d['currency'] ) ? $d['currency'] : '',
		'email'        => isset( $d['customer']['email'] ) ? $d['customer']['email'] : '',
		'error'        => '',
	);
}

/**
 * Verify a webhook signature: HMAC SHA512 of the raw body with the secret key.
 */
function gsley_paystack_verify_signature( $raw_body, $signature ) {
	$secret = gsley_paystack_secret();
	if ( ! $secret || ! $signature ) {
		return false;
	}
	$computed = hash_hmac( 'sha512', $raw_body, $secret );
	return hash_equals( $computed, (string) $signature );
}
