<?php
/**
 * GSLEY Diagnostics — contact form handler.
 *
 * The theme's contact pattern posts (no-JS) to /api/contact. We intercept that
 * path early, validate (honeypot + email + per-IP rate limit), persist the lead,
 * notify the business inbox via Resend, and redirect (POST/redirect/GET) back to
 * the contact page with a status flag.
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

	$contact_url = home_url( '/contact' );

	// Honeypot — pretend success, do nothing.
	if ( ! empty( $_POST['company_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', $contact_url ) );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'email', $contact_url ) );
		exit;
	}

	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key  = 'gsley_contact_rl_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 8 ) {
		wp_safe_redirect( add_query_arg( 'error', 'rate', $contact_url ) );
		exit;
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$org     = isset( $_POST['org'] ) ? sanitize_text_field( wp_unslash( $_POST['org'] ) ) : '';
	$need    = isset( $_POST['need'] ) ? sanitize_text_field( wp_unslash( $_POST['need'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	$lead_id = gsley_insert_lead(
		array(
			'source'       => 'contact',
			'name'         => $name,
			'email'        => $email,
			'organisation' => $org,
			'need'         => $need,
			'message'      => $message,
			'ip'           => $ip,
			'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		)
	);

	// Notify the business inbox (reply-to the enquirer).
	$subject = sprintf( 'New brief from %s%s', $name ? $name : $email, $org ? ' (' . $org . ')' : '' );
	$html    = '<div style="font-family:Helvetica,Arial,sans-serif;color:#1F2C3F;max-width:600px">'
		. '<h2 style="font-family:Georgia,serif;color:#0F1B2D">New contact brief</h2>'
		. '<p><strong>Name:</strong> ' . esc_html( $name ? $name : '&mdash;' ) . '<br>'
		. '<strong>Email:</strong> ' . esc_html( $email ) . '<br>'
		. '<strong>Organisation:</strong> ' . esc_html( $org ? $org : '&mdash;' ) . '<br>'
		. '<strong>Need:</strong> ' . esc_html( $need ? $need : '&mdash;' ) . '</p>'
		. '<p><strong>Message:</strong><br>' . nl2br( esc_html( $message ) ) . '</p>'
		. '<p style="font-size:12px;color:#8895A7">Logged to GSLEY Leads. IP ' . esc_html( $ip ) . '</p></div>';

	$send = gsley_resend_send( gsley_notify_email(), $subject, $html, array( 'reply_to' => $email ) );
	if ( $lead_id ) {
		gsley_update_lead_delivery( $lead_id, $send );
	}

	wp_safe_redirect( add_query_arg( 'sent', '1', $contact_url ) );
	exit;
}
