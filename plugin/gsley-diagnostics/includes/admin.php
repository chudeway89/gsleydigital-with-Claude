<?php
/**
 * GSLEY Diagnostics — WP-admin dashboard + settings.
 *
 * Lead tracking and tool analytics from the custom tables, plus a settings
 * page for the Resend credentials.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'gsley_diagnostics_admin_menu' );
function gsley_diagnostics_admin_menu() {
	add_menu_page( 'GSLEY Leads', 'GSLEY Leads', 'manage_options', 'gsley-leads', 'gsley_render_dashboard', 'dashicons-chart-area', 26 );
	add_submenu_page( 'gsley-leads', 'Lead Dashboard', 'Dashboard', 'manage_options', 'gsley-leads', 'gsley_render_dashboard' );
	add_submenu_page( 'gsley-leads', 'Diagnostics Settings', 'Settings', 'manage_options', 'gsley-diagnostics-settings', 'gsley_render_settings' );
}

add_action( 'admin_init', 'gsley_diagnostics_register_settings' );
function gsley_diagnostics_register_settings() {
	register_setting( 'gsley_diagnostics_settings', 'gsley_resend_api_key', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'gsley_diagnostics_settings', 'gsley_resend_from', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'GSLEY Digital <brief@gsleydigital.com>' ) );
	register_setting( 'gsley_diagnostics_settings', 'gsley_notify_email', array( 'sanitize_callback' => 'sanitize_email', 'default' => 'brief@gsleydigital.com' ) );
}

function gsley_card( $label, $value, $sub = '' ) {
	return '<div style="flex:1 1 160px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px">'
		. '<div style="font-size:28px;font-weight:600;color:#0F1B2D">' . esc_html( $value ) . '</div>'
		. '<div style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:#646970">' . esc_html( $label ) . '</div>'
		. ( $sub ? '<div style="font-size:12px;color:#8c8f94;margin-top:4px">' . esc_html( $sub ) . '</div>' : '' )
		. '</div>';
}

function gsley_render_dashboard() {
	global $wpdb;
	$leads = gsley_table_leads();
	$subs  = gsley_table_submissions();
	$configs = gsley_diagnostics_configs();

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
	$total_leads = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $leads" );
	$unique      = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM $leads" );
	$contact_n   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $leads WHERE source = %s", 'contact' ) );
	$tool_n      = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $leads WHERE source = %s", 'tool' ) );
	$sub_total   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $subs" );
	$sent        = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $subs WHERE delivery_status = %s", 'sent' ) );
	$failed      = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $subs WHERE delivery_status = %s", 'failed' ) );

	$per_tool = $wpdb->get_results( "SELECT tool, COUNT(*) c, AVG(overall) a, SUM(delivery_status='sent') s FROM $subs GROUP BY tool ORDER BY c DESC" );

	$paged    = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$per_page = 25;
	$offset   = ( $paged - 1 ) * $per_page;
	$recent   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $leads ORDER BY created DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
	$max_page = max( 1, (int) ceil( $total_leads / $per_page ) );
	// phpcs:enable

	$rate = $sub_total ? round( $sent / $sub_total * 100 ) . '%' : '—';
	$key_set = gsley_resend_api_key() ? 'configured' : 'not configured';

	echo '<div class="wrap"><h1>GSLEY Leads</h1>';
	if ( ! gsley_resend_api_key() ) {
		echo '<div class="notice notice-warning"><p><strong>Resend isn’t configured yet.</strong> Leads are still captured, but report and notification emails won’t send until you add an API key in <a href="' . esc_url( admin_url( 'admin.php?page=gsley-diagnostics-settings' ) ) . '">Settings</a>.</p></div>';
	}

	echo '<div style="display:flex;flex-wrap:wrap;gap:12px;margin:16px 0">';
	echo gsley_card( 'Leads captured', number_format_i18n( $total_leads ), $unique . ' unique emails' );
	echo gsley_card( 'Contact briefs', number_format_i18n( $contact_n ) );
	echo gsley_card( 'Tool completions', number_format_i18n( $tool_n ) );
	echo gsley_card( 'Reports sent', number_format_i18n( $sent ), $rate . ' delivery · ' . $failed . ' failed' );
	echo '</div>';

	// Per-tool analytics.
	echo '<h2>Tool analytics</h2>';
	if ( $per_tool ) {
		echo '<table class="widefat striped"><thead><tr><th>Tool</th><th>Completions</th><th>Avg score</th><th>Reports sent</th></tr></thead><tbody>';
		foreach ( $per_tool as $row ) {
			$title  = isset( $configs[ $row->tool ] ) ? $configs[ $row->tool ]['title'] : $row->tool;
			$engine = isset( $configs[ $row->tool ] ) ? $configs[ $row->tool ]['engine'] : 'likert';
			$avg    = ( null !== $row->a && 'finder' !== $engine ) ? ( 'calculator' === $engine ? '≈ ₦' . number_format_i18n( (float) $row->a ) : round( (float) $row->a ) . '/100' ) : '—';
			echo '<tr><td><strong>' . esc_html( $title ) . '</strong></td><td>' . (int) $row->c . '</td><td>' . esc_html( $avg ) . '</td><td>' . (int) $row->s . '</td></tr>';
		}
		echo '</tbody></table>';
	} else {
		echo '<p>No tool completions yet.</p>';
	}

	// Recent leads.
	echo '<h2 style="margin-top:24px">Recent leads</h2>';
	if ( $recent ) {
		echo '<table class="widefat striped"><thead><tr><th>When</th><th>Source</th><th>Name</th><th>Email</th><th>Organisation</th><th>Tool / Need</th><th>Email status</th></tr></thead><tbody>';
		foreach ( $recent as $l ) {
			$when = esc_html( mysql2date( 'Y-m-d H:i', $l->created ) );
			$src  = 'tool' === $l->source ? '◇ Tool' : '▲ Contact';
			$what = 'tool' === $l->source
				? ( isset( $configs[ $l->tool ] ) ? $configs[ $l->tool ]['title'] : $l->tool )
				: $l->need;
			$status = $l->delivery_status;
			$colour = 'sent' === $status ? '#15803D' : ( 'failed' === $status ? '#C0392B' : '#8c8f94' );
			$badge  = '<span style="color:' . $colour . '">' . esc_html( $status ) . '</span>';
			if ( 'failed' === $status && $l->delivery_error ) {
				$badge .= ' <span title="' . esc_attr( $l->delivery_error ) . '" style="color:#8c8f94;cursor:help">(?)</span>';
			}
			echo '<tr>'
				. '<td>' . $when . '</td>'
				. '<td>' . esc_html( $src ) . '</td>'
				. '<td>' . esc_html( $l->name ) . '</td>'
				. '<td><a href="mailto:' . esc_attr( $l->email ) . '">' . esc_html( $l->email ) . '</a></td>'
				. '<td>' . esc_html( $l->organisation ) . '</td>'
				. '<td>' . esc_html( $what ) . '</td>'
				. '<td>' . $badge . '</td>'
				. '</tr>';
			if ( 'contact' === $l->source && $l->message ) {
				echo '<tr><td></td><td colspan="6" style="color:#646970;font-style:italic">' . esc_html( wp_trim_words( $l->message, 40 ) ) . '</td></tr>';
			}
		}
		echo '</tbody></table>';

		if ( $max_page > 1 ) {
			echo '<p style="margin-top:12px">';
			if ( $paged > 1 ) {
				echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $paged - 1 ) ) . '">‹ Newer</a> ';
			}
			echo '<span style="margin:0 8px">Page ' . (int) $paged . ' of ' . (int) $max_page . '</span>';
			if ( $paged < $max_page ) {
				echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $paged + 1 ) ) . '">Older ›</a>';
			}
			echo '</p>';
		}
	} else {
		echo '<p>No leads captured yet. They’ll appear here as the contact form and tools are used.</p>';
	}

	echo '<p style="color:#8c8f94;margin-top:20px">Resend: ' . esc_html( $key_set ) . '. Email delivery status is logged per row.</p>';
	echo '</div>';
}

function gsley_render_settings() {
	echo '<div class="wrap"><h1>Diagnostics Settings</h1>';
	echo '<p>All transactional email (tool reports + contact notifications) is sent through <strong>Resend</strong>.</p>';
	if ( defined( 'GSLEY_RESEND_API_KEY' ) && GSLEY_RESEND_API_KEY ) {
		echo '<div class="notice notice-info inline"><p>An API key is set in <code>wp-config.php</code> (<code>GSLEY_RESEND_API_KEY</code>); it overrides the field below.</p></div>';
	}
	echo '<form method="post" action="options.php">';
	settings_fields( 'gsley_diagnostics_settings' );
	echo '<table class="form-table" role="presentation"><tbody>';
	echo '<tr><th scope="row"><label for="gsley_resend_api_key">Resend API key</label></th>'
		. '<td><input type="password" autocomplete="off" id="gsley_resend_api_key" name="gsley_resend_api_key" value="' . esc_attr( get_option( 'gsley_resend_api_key', '' ) ) . '" class="regular-text">'
		. '<p class="description">From the Resend dashboard. For production, prefer setting <code>GSLEY_RESEND_API_KEY</code> in <code>wp-config.php</code>.</p></td></tr>';
	echo '<tr><th scope="row"><label for="gsley_resend_from">From address</label></th>'
		. '<td><input type="text" id="gsley_resend_from" name="gsley_resend_from" value="' . esc_attr( get_option( 'gsley_resend_from', 'GSLEY Digital <brief@gsleydigital.com>' ) ) . '" class="regular-text">'
		. '<p class="description">Must be a verified domain/sender in Resend.</p></td></tr>';
	echo '<tr><th scope="row"><label for="gsley_notify_email">Notification inbox</label></th>'
		. '<td><input type="email" id="gsley_notify_email" name="gsley_notify_email" value="' . esc_attr( get_option( 'gsley_notify_email', 'brief@gsleydigital.com' ) ) . '" class="regular-text">'
		. '<p class="description">Where new contact briefs are sent. Tool reports go to the person who took the diagnostic.</p></td></tr>';
	echo '</tbody></table>';
	submit_button();
	echo '</form></div>';
}
