<?php
/**
 * GSLEY Diagnostics — contact form handler.
 *
 * The Contact page's brief form posts to /api/contact. With JavaScript it sends
 * JSON and gets JSON back (the page shows sending / success / error states);
 * without JavaScript it posts a normal form and is redirected back to
 * /contact/?sent=1 (POST/redirect/GET). Either way we validate (honeypot,
 * required fields, email, per-IP rate limit), persist the lead, and notify the
 * business inbox via Resend.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'gsley_maybe_handle_contact', 5 );

function gsley_maybe_handle_contact() {
	if ( empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	if ( trim( (string) $path, '/' ) !== 'api/contact' ) {
		return;
	}

	$type = isset( $_SERVER['CONTENT_TYPE'] ) ? strtolower( (string) $_SERVER['CONTENT_TYPE'] ) : '';
	$json = false !== strpos( $type, 'application/json' );
	if ( $json ) {
		$body = json_decode( (string) file_get_contents( 'php://input' ), true );
		$in   = is_array( $body ) ? $body : array();
	} else {
		$in = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- public form; honeypot + rate limit.
	}
	$get = function ( $k ) use ( $in ) {
		return isset( $in[ $k ] ) && is_scalar( $in[ $k ] ) ? (string) $in[ $k ] : '';
	};

	$result = gsley_handle_contact_submission(
		array(
			'name'     => $get( 'name' ),
			'email'    => $get( 'email' ),
			'org'      => $get( 'org' ),
			'phone'    => $get( 'phone' ),
			'need'     => $get( 'need' ),
			'message'  => $get( 'message' ),
			'source'   => $get( 'source' ),
			// Honeypot: v2 form uses `website`; the earlier form used `company_website`.
			'honeypot' => $get( 'website' ) . $get( 'company_website' ),
		)
	);

	if ( $json ) {
		nocache_headers();
		wp_send_json( array( 'ok' => $result['ok'], 'error' => $result['error'] ), $result['ok'] ? 200 : ( 'rate' === $result['error'] ? 429 : 400 ) );
	}

	$contact_url = home_url( '/contact/' );
	$url         = $result['ok'] ? add_query_arg( 'sent', '1', $contact_url ) : add_query_arg( 'error', $result['error'], $contact_url );
	wp_safe_redirect( $url . '#brief' );
	exit;
}

/**
 * Validate, persist and notify.
 *
 * @param array $f Raw (unslashed) fields.
 * @return array { ok:bool, error:string }
 */
function gsley_handle_contact_submission( $f ) {
	// Honeypot — report success, do nothing.
	if ( '' !== trim( $f['honeypot'] ) ) {
		return array( 'ok' => true, 'error' => '' );
	}

	$email   = sanitize_email( $f['email'] );
	$name    = sanitize_text_field( $f['name'] );
	$message = sanitize_textarea_field( $f['message'] );
	if ( ! is_email( $email ) ) {
		return array( 'ok' => false, 'error' => 'email' );
	}
	if ( '' === $name || '' === trim( $message ) ) {
		return array( 'ok' => false, 'error' => 'required' );
	}

	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key  = 'gsley_contact_rl_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 8 ) {
		return array( 'ok' => false, 'error' => 'rate' );
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$org    = sanitize_text_field( $f['org'] );
	$phone  = substr( sanitize_text_field( $f['phone'] ), 0, 64 );
	$need   = sanitize_key( $f['need'] );
	$source = substr( sanitize_text_field( $f['source'] ), 0, 200 );

	// Human label for the chosen service.
	$need_label = $need;
	if ( 'not-sure' === $need ) {
		$need_label = 'Not sure yet — advise me';
	} elseif ( $need && function_exists( 'gsley_service' ) && gsley_service( $need ) ) {
		$svc        = gsley_service( $need );
		$need_label = $svc['label'] . ' (' . ucfirst( $svc['pillar'] ) . ')';
	}

	$lead_id = gsley_insert_lead(
		array(
			'source'       => 'contact',
			'name'         => $name,
			'email'        => $email,
			'organisation' => $org,
			'phone'        => $phone,
			'referrer'     => $source,
			'need'         => $need_label,
			'message'      => $message,
			'ip'           => $ip,
			'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		)
	);
	if ( ! $lead_id ) {
		return array( 'ok' => false, 'error' => 'server' );
	}

	// Notify the business inbox (reply-to the enquirer).
	$subject = sprintf( 'New brief from %s%s', $name ? $name : $email, $org ? ' (' . $org . ')' : '' );
	$html    = '<div style="font-family:Helvetica,Arial,sans-serif;color:#1F2C3F;max-width:600px">'
		. '<h2 style="font-family:Georgia,serif;color:#0F1B2D">New contact brief</h2>'
		. '<p><strong>Name:</strong> ' . esc_html( $name ) . '<br>'
		. '<strong>Email:</strong> ' . esc_html( $email ) . '<br>'
		. '<strong>Phone / WhatsApp:</strong> ' . esc_html( $phone ? $phone : '—' ) . '<br>'
		. '<strong>Organisation:</strong> ' . esc_html( $org ? $org : '—' ) . '<br>'
		. '<strong>Need:</strong> ' . esc_html( $need_label ? $need_label : '—' ) . '</p>'
		. '<p><strong>Message:</strong><br>' . nl2br( esc_html( $message ) ) . '</p>'
		. '<p style="font-size:12px;color:#5F6B7C">Logged to GSLEY Leads. Came from: ' . esc_html( $source ? $source : 'direct' ) . ' · IP ' . esc_html( $ip ) . '</p></div>';

	$send = gsley_resend_send( gsley_notify_email(), $subject, $html, array( 'reply_to' => $email ) );
	gsley_update_lead_delivery( $lead_id, $send );

	// The lead is saved even if the email fails; the dashboard shows the delivery status.
	return array( 'ok' => true, 'error' => '' );
}
