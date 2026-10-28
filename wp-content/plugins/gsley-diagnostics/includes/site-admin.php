<?php
/**
 * GSLEY Diagnostics — "GSLEY Settings" admin screen (site facts).
 *
 * Every email, phone, WhatsApp link, booking button, social link and office
 * on the site reads from here. Change a value once; it updates everywhere.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'gsley_site_admin_menu', 12 );
function gsley_site_admin_menu() {
	add_submenu_page( 'gsley-leads', 'GSLEY Settings', 'GSLEY Settings', 'manage_options', 'gsley-site-settings', 'gsley_render_site_settings' );
}

add_action( 'admin_init', 'gsley_site_register_setting' );
function gsley_site_register_setting() {
	register_setting( 'gsley_site', 'gsley_site', array( 'type' => 'array', 'sanitize_callback' => 'gsley_site_sanitize', 'default' => array() ) );
}

/** Field definitions: key => [label, type, help]. */
function gsley_site_fields() {
	return array(
		'Contact'  => array(
			'email'         => array( 'Email', 'email', 'Shown in the footer, Contact page and FAQ.' ),
			'phone'         => array( 'Phone (as displayed)', 'text', 'e.g. +234 902 476 7079' ),
			'phone_tel'     => array( 'Phone (for dialling)', 'text', 'Digits with country code, e.g. +2349024767079' ),
			'whatsapp'      => array( 'WhatsApp number', 'text', 'Digits only, with country code, e.g. 2349024767079' ),
			'whatsapp_text' => array( 'WhatsApp pre-filled message', 'text', '' ),
			'location'      => array( 'Location line', 'text', 'e.g. Lagos · Enugu, Nigeria · working worldwide' ),
			'hours'         => array( 'Office hours', 'text', 'Leave empty unless you can keep to them — nothing is shown when empty.' ),
		),
		'Booking'  => array(
			'booking_url' => array( 'Booking link (Cal.com or Calendly)', 'url', 'When set, every “Book a Discovery Call” button opens this link and Contact shows the scheduler. When empty, the buttons go to the brief form on Contact.' ),
		),
		'Brand'    => array(
			'tagline' => array( 'Footer tagline', 'textarea', '' ),
		),
		'Social'   => array(
			'linkedin'  => array( 'LinkedIn', 'url', '' ),
			'x'         => array( 'X', 'url', '' ),
			'instagram' => array( 'Instagram', 'url', '' ),
			'facebook'  => array( 'Facebook', 'url', '' ),
			'youtube'   => array( 'YouTube', 'url', 'Leave any social field empty to hide it.' ),
		),
	);
}

function gsley_site_sanitize( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$out = array();
	foreach ( gsley_site_fields() as $fields ) {
		foreach ( $fields as $key => $f ) {
			$v = isset( $in[ $key ] ) ? wp_unslash( $in[ $key ] ) : '';
			switch ( $f[1] ) {
				case 'email':
					$out[ $key ] = sanitize_email( $v );
					break;
				case 'url':
					$out[ $key ] = esc_url_raw( trim( $v ) );
					break;
				case 'textarea':
					$out[ $key ] = sanitize_textarea_field( $v );
					break;
				default:
					$out[ $key ] = sanitize_text_field( $v );
			}
		}
	}
	$out['offices'] = array();
	if ( ! empty( $in['offices'] ) && is_array( $in['offices'] ) ) {
		foreach ( $in['offices'] as $o ) {
			$row = array(
				'city'     => sanitize_text_field( wp_unslash( isset( $o['city'] ) ? $o['city'] : '' ) ),
				'street'   => sanitize_text_field( wp_unslash( isset( $o['street'] ) ? $o['street'] : '' ) ),
				'postcode' => sanitize_text_field( wp_unslash( isset( $o['postcode'] ) ? $o['postcode'] : '' ) ),
				'phone'    => sanitize_text_field( wp_unslash( isset( $o['phone'] ) ? $o['phone'] : '' ) ),
				'maps'     => esc_url_raw( trim( wp_unslash( isset( $o['maps'] ) ? $o['maps'] : '' ) ) ),
			);
			if ( '' !== $row['city'] ) {
				$out['offices'][] = $row;
			}
		}
	}
	return $out;
}

function gsley_render_site_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = gsley_site_settings();
	echo '<div class="wrap"><h1>GSLEY Settings</h1>';
	echo '<p style="max-width:720px">The single source for the site’s facts. The header, footer, Contact page, FAQ, WhatsApp button, mobile action bar and the Organization schema all read from here.</p>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'gsley_site' );

	foreach ( gsley_site_fields() as $group => $fields ) {
		echo '<h2>' . esc_html( $group ) . '</h2><table class="form-table" role="presentation">';
		foreach ( $fields as $key => $f ) {
			$id  = 'gsley_site_' . $key;
			$val = isset( $s[ $key ] ) ? $s[ $key ] : '';
			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
			if ( 'textarea' === $f[1] ) {
				echo '<textarea class="large-text" rows="3" id="' . esc_attr( $id ) . '" name="gsley_site[' . esc_attr( $key ) . ']">' . esc_textarea( $val ) . '</textarea>';
			} else {
				echo '<input class="regular-text" type="' . esc_attr( 'email' === $f[1] ? 'email' : ( 'url' === $f[1] ? 'url' : 'text' ) ) . '" id="' . esc_attr( $id ) . '" name="gsley_site[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '">';
			}
			if ( $f[2] ) {
				echo '<p class="description">' . esc_html( $f[2] ) . '</p>';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	// Offices repeater: existing rows + two blank rows.
	$offices   = gsley_site_offices();
	$offices[] = array();
	$offices[] = array();
	echo '<h2>Offices</h2><p class="description" style="max-width:720px">Lagos first — it is the primary office for search. Street addresses appear on Contact and in the Organization schema only once filled in; add only verified addresses (the same ones as your Google Business Profiles). Clear the city to remove a row.</p>';
	echo '<table class="widefat striped" style="max-width:1100px;margin-top:8px"><thead><tr><th>City</th><th>Street address</th><th>Postcode</th><th>Phone</th><th>Google Maps link</th></tr></thead><tbody>';
	foreach ( $offices as $i => $o ) {
		$o = wp_parse_args( $o, array( 'city' => '', 'street' => '', 'postcode' => '', 'phone' => '', 'maps' => '' ) );
		echo '<tr>';
		foreach ( array( 'city' => 'text', 'street' => 'text', 'postcode' => 'text', 'phone' => 'text', 'maps' => 'url' ) as $k => $t ) {
			echo '<td><input style="width:100%" type="' . esc_attr( $t ) . '" name="gsley_site[offices][' . (int) $i . '][' . esc_attr( $k ) . ']" value="' . esc_attr( $o[ $k ] ) . '" aria-label="' . esc_attr( ucfirst( $k ) . ' ' . ( $i + 1 ) ) . '"></td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';

	submit_button();
	echo '</form>';

	echo '<hr><h2>Using these values in content</h2><ul style="list-style:disc;margin-left:20px;max-width:760px">';
	echo '<li><code>[gsley key="email"]</code>, <code>[gsley key="phone"]</code>, <code>[gsley key="location"]</code> — a value as text.</li>';
	echo '<li><code>[gsley_link type="book" class="btn btn--solid"]Book a Discovery Call[/gsley_link]</code> — a link (types: email, phone, whatsapp, book).</li>';
	echo '<li><code>[gsley_count type="all-tools" cap="true"]</code> — a number as a word from the registry (types: all-tools, all-tools-others, free-tools, free-tools-others, market-services, digital-services, health-services, all-services).</li>';
	echo '<li>In the block editor, a Paragraph or Button can also be bound to a value through the <em>GSLEY Settings</em> bindings source.</li>';
	echo '</ul></div>';
}
