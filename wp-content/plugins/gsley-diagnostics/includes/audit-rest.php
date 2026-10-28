<?php
/**
 * GSLEY Diagnostics — REST for the paid audit tools.
 *
 * Flow: run (free scored report) → pay/init (Paystack) → pay/confirm (verify &
 * unlock recommendations + email) ; webhook is the durable backstop. Detailed
 * recommendations are NEVER returned until payment is verified server-side.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'gsley_audit_register_routes' );
function gsley_audit_register_routes() {
	$pub = '__return_true';
	register_rest_route( 'gsley/v1', '/audit/(?P<tool>[a-z0-9-]+)', array( 'methods' => 'POST', 'permission_callback' => $pub, 'callback' => 'gsley_audit_rest_run' ) );
	register_rest_route( 'gsley/v1', '/pay/init', array( 'methods' => 'POST', 'permission_callback' => $pub, 'callback' => 'gsley_audit_rest_pay_init' ) );
	register_rest_route( 'gsley/v1', '/pay/confirm', array( 'methods' => 'POST', 'permission_callback' => $pub, 'callback' => 'gsley_audit_rest_pay_confirm' ) );
	register_rest_route( 'gsley/v1', '/pay/webhook', array( 'methods' => 'POST', 'permission_callback' => $pub, 'callback' => 'gsley_audit_rest_webhook' ) );
}

function gsley_audit_is_audit_tool( $tool ) {
	$c = gsley_diagnostics_configs();
	return isset( $c[ $tool ] ) && 'audit' === $c[ $tool ]['engine'];
}

/** Run a free audit and store it (no email, no recommendations returned). */
function gsley_audit_rest_run( $request ) {
	$tool = sanitize_key( $request['tool'] );
	if ( ! gsley_audit_is_audit_tool( $tool ) ) {
		return new WP_Error( 'gsley_unknown_tool', 'Unknown audit.', array( 'status' => 404 ) );
	}
	$body = $request->get_json_params();
	$body = is_array( $body ) ? $body : array();
	if ( ! empty( $body['company_website'] ) ) { // honeypot
		return new WP_REST_Response( array( 'ok' => false ), 200 );
	}

	// Audits fetch third-party pages, so they are limited per IP and site-wide.
	$limited = gsley_guard( 'audit' );
	if ( $limited ) {
		return new WP_Error( 'gsley_rate', 'Too many audits in a short time. Please wait a few minutes.', array( 'status' => 429 ) );
	}

	$inputs = array(
		'website'        => isset( $body['website'] ) ? gsley_clip( $body['website'], 300 ) : '',
		'socials'        => array(),
		'google_rating'  => isset( $body['google_rating'] ) ? max( 0, min( 5, (float) $body['google_rating'] ) ) : 0,
		'google_reviews' => isset( $body['google_reviews'] ) ? max( 0, min( 10000000, (int) $body['google_reviews'] ) ) : 0,
	);
	if ( isset( $body['socials'] ) && is_array( $body['socials'] ) ) {
		foreach ( array_slice( $body['socials'], 0, 8 ) as $s ) { // at most 8 profiles per audit
			$s = gsley_clip( $s, 300 );
			if ( '' !== trim( $s ) ) {
				$inputs['socials'][] = $s;
			}
		}
	}

	$cfg = gsley_diagnostics_configs()[ $tool ];
	$run = gsley_audit_run( $tool, $inputs );

	$token = wp_generate_password( 24, false );
	$sub_id = gsley_insert_submission(
		array(
			'tool'     => $tool,
			'overall'  => (int) $run['overall'],
			'result'   => wp_json_encode( array( 'inputs' => $inputs, 'run' => $run ) ),
			'amount'   => (int) $cfg['price'],
			'currency' => gsley_paystack_currency(), // the currency the checkout will charge in
			'paid'     => 0,
			'token'    => $token,
		)
	);

	return new WP_REST_Response(
		array(
			'audit_id'   => $sub_id,
			'token'      => $token,
			'price'      => (int) $cfg['price'],
			'currency'   => $cfg['currency'],
			'priceLabel' => $cfg['priceLabel'],
			'report'     => array(
				'overall'  => $run['overall'],
				'pillars'  => $run['pillars'],
				'findings' => $run['findings'],
				'summary'  => $run['summary'],
				'reco_count' => count( $run['recommendations'] ),
			),
		),
		200
	);
}

/** Start a Paystack payment for an audit. */
function gsley_audit_rest_pay_init( $request ) {
	$body = $request->get_json_params();
	$body = is_array( $body ) ? $body : array();
	$id    = isset( $body['audit_id'] ) ? (int) $body['audit_id'] : 0;
	$token = isset( $body['token'] ) ? gsley_clip( $body['token'], 64 ) : '';
	$email = isset( $body['email'] ) ? sanitize_email( gsley_clip( $body['email'], 190 ) ) : '';

	if ( gsley_guard( 'payinit', $email ) ) {
		return new WP_Error( 'gsley_rate', 'Too many attempts. Please wait a few minutes.', array( 'status' => 429 ) );
	}
	$sub = gsley_get_submission( $id );
	if ( ! $sub || '' === (string) $sub->token || ! hash_equals( (string) $sub->token, $token ) ) {
		return new WP_Error( 'gsley_bad', 'Invalid audit reference.', array( 'status' => 403 ) );
	}
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'gsley_email', 'A valid email is required.', array( 'status' => 422 ) );
	}
	if ( (int) $sub->paid ) {
		return new WP_REST_Response( array( 'paid' => true ), 200 );
	}
	if ( ! gsley_paystack_ready() ) {
		return new WP_Error( 'gsley_pay', 'Payments are not configured yet.', array( 'status' => 503 ) );
	}

	$cfg  = gsley_diagnostics_configs()[ $sub->tool ];
	$ref  = 'gsleyaud_' . $id . '_' . wp_generate_password( 10, false );

	// Validate the callback URL is on this site, else fall back to the tool page.
	// The buyer always returns to this tool's page on this site (no caller-chosen URLs).
	$callback = trailingslashit( home_url( '/tools/' . $sub->tool ) );

	// Capture/refresh the lead now that we have an email + intent.
	$lead_id = (int) $sub->lead_id;
	if ( ! $lead_id ) {
		$lead_id = gsley_insert_lead( array( 'source' => 'tool', 'tool' => $sub->tool, 'email' => $email ) );
	}

	$init = gsley_paystack_init(
		$email,
		(float) $sub->amount,
		$ref,
		$callback,
		array( 'audit_id' => $id, 'tool' => $sub->tool ),
		$sub->currency
	);
	if ( ! $init['ok'] ) {
		do_action( 'gsley_security_event', 'payment_init_failed', array( 'audit' => $id, 'detail' => $init['error'] ) );
		return new WP_Error( 'gsley_pay_init', 'We couldn’t start the checkout. Please try again in a moment.', array( 'status' => 502 ) );
	}

	gsley_update_submission( $id, array( 'payment_ref' => $init['reference'], 'lead_id' => $lead_id ) );

	return new WP_REST_Response( array( 'authorization_url' => $init['authorization_url'] ), 200 );
}

/** Confirm a payment and unlock recommendations. */
function gsley_audit_rest_pay_confirm( $request ) {
	$body = $request->get_json_params();
	$body = is_array( $body ) ? $body : array();
	$id    = isset( $body['audit_id'] ) ? (int) $body['audit_id'] : 0;
	$token = isset( $body['token'] ) ? gsley_clip( $body['token'], 64 ) : '';
	$ref   = isset( $body['reference'] ) ? gsley_clip( $body['reference'], 100 ) : '';

	if ( gsley_guard( 'payconf' ) ) {
		return new WP_Error( 'gsley_rate', 'Too many attempts. Please wait a few minutes.', array( 'status' => 429 ) );
	}
	$sub = gsley_get_submission( $id );
	if ( ! $sub || '' === (string) $sub->token || ! hash_equals( (string) $sub->token, $token ) ) {
		return new WP_Error( 'gsley_bad', 'Invalid audit reference.', array( 'status' => 403 ) );
	}

	$stored = json_decode( (string) $sub->result, true );
	$run    = isset( $stored['run'] ) ? $stored['run'] : array();

	if ( (int) $sub->paid ) {
		return new WP_REST_Response( array( 'paid' => true, 'recommendations' => isset( $run['recommendations'] ) ? $run['recommendations'] : array() ), 200 );
	}

	// Only accept the reference we issued.
	if ( ! $ref || ! hash_equals( (string) $sub->payment_ref, $ref ) ) {
		return new WP_Error( 'gsley_ref', 'Unknown payment reference.', array( 'status' => 422 ) );
	}

	$v = gsley_paystack_verify( $ref );
	if ( ! $v['ok'] || ! $v['success'] ) {
		return new WP_REST_Response( array( 'paid' => false, 'error' => 'Payment not completed.' ), 200 );
	}
	if ( ! gsley_payment_matches( $sub, $v ) ) {
		do_action( 'gsley_security_event', 'payment_mismatch', array( 'audit' => $id, 'reference' => $ref ) );
		return new WP_REST_Response( array( 'paid' => false, 'error' => 'Payment could not be matched to this audit.' ), 200 );
	}

	gsley_audit_mark_paid_and_email( $sub, $v['email'] );

	return new WP_REST_Response( array( 'paid' => true, 'recommendations' => isset( $run['recommendations'] ) ? $run['recommendations'] : array() ), 200 );
}

/** A verified payment must be for this audit's exact price and currency. */
function gsley_payment_matches( $sub, $v ) {
	$expected_currency = $sub->currency ? strtoupper( (string) $sub->currency ) : gsley_paystack_currency();
	return abs( (float) $v['amount_major'] - (float) $sub->amount ) < 0.005
		&& strtoupper( (string) $v['currency'] ) === $expected_currency;
}

/** Paystack webhook — durable backstop. */
function gsley_audit_rest_webhook( $request ) {
	$raw = $request->get_body();
	$sig = $request->get_header( 'x-paystack-signature' );
	if ( ! gsley_paystack_verify_signature( $raw, $sig ) ) {
		do_action( 'gsley_security_event', 'webhook_bad_signature', array() );
		return new WP_Error( 'gsley_sig', 'Invalid signature.', array( 'status' => 401 ) );
	}
	$data = json_decode( $raw, true );
	if ( isset( $data['event'] ) && 'charge.success' === $data['event'] && ! empty( $data['data']['reference'] ) ) {
		global $wpdb;
		$ref = sanitize_text_field( $data['data']['reference'] );
		$sub = '' === $ref ? null : $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . gsley_table_submissions() . ' WHERE payment_ref = %s', $ref ) ); // phpcs:ignore WordPress.DB
		$v   = array(
			'amount_major' => isset( $data['data']['amount'] ) ? ( (int) $data['data']['amount'] ) / 100 : 0,
			'currency'     => isset( $data['data']['currency'] ) ? (string) $data['data']['currency'] : '',
		);
		if ( $sub && ! (int) $sub->paid && gsley_payment_matches( $sub, $v ) ) {
			$email = isset( $data['data']['customer']['email'] ) ? sanitize_email( $data['data']['customer']['email'] ) : '';
			gsley_audit_mark_paid_and_email( $sub, $email );
		}
	}
	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/** Mark an audit paid and email the unlocked recommendations. Idempotent. */
function gsley_audit_mark_paid_and_email( $sub, $email ) {
	if ( (int) $sub->paid ) {
		return;
	}
	gsley_update_submission( $sub->id, array( 'paid' => 1 ) );

	// Ensure a lead exists with this email.
	if ( ! (int) $sub->lead_id && $email && is_email( $email ) ) {
		$lead_id = gsley_insert_lead( array( 'source' => 'tool', 'tool' => $sub->tool, 'email' => $email ) );
		gsley_update_submission( $sub->id, array( 'lead_id' => $lead_id ) );
	}

	$cfg    = gsley_diagnostics_configs();
	$cfg    = isset( $cfg[ $sub->tool ] ) ? $cfg[ $sub->tool ] : null;
	$stored = json_decode( (string) $sub->result, true );
	$run    = isset( $stored['run'] ) ? $stored['run'] : array();
	if ( ! $email || ! is_email( $email ) || ! $cfg ) {
		return;
	}
	$mail = gsley_audit_email_html( $cfg, $run );
	$send = gsley_resend_send( $email, $mail['subject'], $mail['html'], array( 'reply_to' => gsley_notify_email() ) );
	gsley_update_submission_delivery( $sub->id, $send );
}

/** Build the paid recommendations email. */
function gsley_audit_email_html( $cfg, $run ) {
	$recs = isset( $run['recommendations'] ) ? $run['recommendations'] : array();
	$rows = '';
	foreach ( $recs as $r ) {
		$pri = strtoupper( $r['priority'] );
		$col = 'high' === $r['priority'] ? '#C0392B' : ( 'medium' === $r['priority'] ? '#B7791F' : '#4A5568' );
		$rows .= '<div style="margin:14px 0;padding:14px 16px;border:1px solid #E5E2D8;border-left:3px solid ' . $col . ';border-radius:8px">'
			. '<div style="font-family:monospace;font-size:11px;letter-spacing:.08em;color:' . $col . '">' . esc_html( $pri ) . '</div>'
			. '<div style="font-weight:600;color:#0F1B2D;margin:4px 0">' . esc_html( $r['title'] ) . '</div>'
			. '<div style="font-size:14px;color:#1F2C3F">' . esc_html( $r['detail'] ) . '</div>'
			. '<div style="font-size:12px;color:#8895A7;margin-top:6px">Why: ' . esc_html( $r['evidence'] ) . '</div></div>';
	}
	$pillars = '';
	foreach ( ( isset( $run['pillars'] ) ? $run['pillars'] : array() ) as $p ) {
		$pillars .= '<span style="display:inline-block;margin:2px 8px 2px 0;font-size:13px;color:#1F2C3F">' . esc_html( $p['label'] ) . ': <strong>' . (int) $p['pct'] . '</strong>/100</span>';
	}
	$html = '<div style="max-width:600px;margin:0 auto;font-family:Helvetica,Arial,sans-serif;color:#1F2C3F">'
		. '<div style="background:#0F1B2D;color:#FAF9F5;padding:24px 28px;border-radius:10px 10px 0 0">'
		. '<div style="font-family:monospace;font-size:11px;letter-spacing:.12em;color:#16B8A6">GSLEY · ' . esc_html( $cfg['title'] ) . '</div>'
		. '<div style="font-family:Georgia,serif;font-size:30px;margin-top:8px">' . (int) ( $run['overall'] ?? 0 ) . '<span style="font-size:15px;color:#8895A7">/100</span></div>'
		. '<div style="margin-top:6px">' . $pillars . '</div></div>'
		. '<div style="border:1px solid #E5E2D8;border-top:none;border-radius:0 0 10px 10px;padding:24px 28px;background:#FAF9F5">'
		. '<p>Thank you — your detailed recommendations are below, prioritised from the real checks we ran.</p>'
		. $rows
		. '<div style="margin-top:24px;padding-top:18px;border-top:1px solid #E5E2D8">'
		. '<a href="https://gsleydigital.com/contact/" style="display:inline-block;background:#16B8A6;color:#0F1B2D;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:600">Want us to fix these for you? Book a call &#8599;</a></div>'
		. '<p style="font-size:12px;color:#8895A7;margin-top:20px">You purchased this report from GSLEY Digital. Questions? Reply to this email.</p>'
		. '</div></div>';
	return array( 'subject' => $cfg['title'] . ' — your detailed recommendations', 'html' => $html );
}
