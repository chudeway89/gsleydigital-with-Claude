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
	if ( isset( $_GET['view'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		gsley_render_lead_detail( (int) $_GET['view'] );
		return;
	}
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

	$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=gsley_export_leads' ), 'gsley_export_leads' );
	echo '<div class="wrap"><h1 class="wp-heading-inline">GSLEY Leads</h1> <a class="page-title-action" href="' . esc_url( $export_url ) . '">Export CSV</a><hr class="wp-header-end">';
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
		$status_colours = array( 'new' => '#2271b1', 'contacted' => '#B7791F', 'qualified' => '#16B8A6', 'won' => '#15803D', 'lost' => '#8c8f94' );
		echo '<table class="widefat striped"><thead><tr><th>When</th><th>Source</th><th>Name</th><th>Email</th><th>Organisation</th><th>Tool / Need</th><th>Follow-up</th><th>Email status</th><th></th></tr></thead><tbody>';
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
			$fstatus = isset( $l->status ) && $l->status ? $l->status : 'new';
			$fcolour = isset( $status_colours[ $fstatus ] ) ? $status_colours[ $fstatus ] : '#2271b1';
			$view    = admin_url( 'admin.php?page=gsley-leads&view=' . (int) $l->id );
			echo '<tr>'
				. '<td>' . $when . '</td>'
				. '<td>' . esc_html( $src ) . '</td>'
				. '<td>' . esc_html( $l->name ) . '</td>'
				. '<td><a href="mailto:' . esc_attr( $l->email ) . '">' . esc_html( $l->email ) . '</a></td>'
				. '<td>' . esc_html( $l->organisation ) . '</td>'
				. '<td>' . esc_html( $what ) . '</td>'
				. '<td><span style="color:' . esc_attr( $fcolour ) . ';font-weight:600;text-transform:capitalize">' . esc_html( $fstatus ) . '</span></td>'
				. '<td>' . $badge . '</td>'
				. '<td><a class="button button-small" href="' . esc_url( $view ) . '">View</a></td>'
				. '</tr>';
			if ( 'contact' === $l->source && $l->message ) {
				echo '<tr><td></td><td colspan="8" style="color:#646970;font-style:italic">' . esc_html( wp_trim_words( $l->message, 40 ) ) . '</td></tr>';
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

/**
 * Per-lead detail: contact, follow-up status/notes, and exactly what the
 * visitor produced (their answers/inputs and the result they got).
 */
function gsley_render_lead_detail( $id ) {
	global $wpdb;
	$leads = gsley_table_leads();
	$subs  = gsley_table_submissions();

	$saved = false;
	if ( isset( $_POST['gsley_save_lead'] ) && check_admin_referer( 'gsley_save_lead_' . $id ) && current_user_can( 'manage_options' ) ) {
		gsley_update_lead_status(
			$id,
			isset( $_POST['lead_status'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_status'] ) ) : 'new',
			isset( $_POST['lead_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lead_notes'] ) ) : ''
		);
		$saved = true;
	}

	$lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $leads WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB

	echo '<div class="wrap"><h1><a href="' . esc_url( admin_url( 'admin.php?page=gsley-leads' ) ) . '">GSLEY Leads</a> &rsaquo; Lead #' . (int) $id . '</h1>';
	if ( ! $lead ) {
		echo '<p>Lead not found.</p></div>';
		return;
	}
	if ( $saved ) {
		echo '<div class="notice notice-success is-dismissible"><p>Saved.</p></div>';
	}

	$reply = 'mailto:' . rawurlencode( $lead->email ) . '?subject=' . rawurlencode( 'Re: your GSLEY ' . ( 'tool' === $lead->source ? 'diagnostic' : 'enquiry' ) );

	echo '<div style="display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start">';

	// Left: contact + follow-up.
	echo '<div style="flex:1 1 320px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px">';
	echo '<h2 style="margin-top:0">' . esc_html( $lead->name ? $lead->name : '(no name)' ) . '</h2>';
	echo '<p style="margin:.2em 0"><a href="' . esc_attr( $reply ) . '">' . esc_html( $lead->email ) . '</a></p>';
	if ( $lead->organisation ) {
		echo '<p style="margin:.2em 0;color:#646970">' . esc_html( $lead->organisation ) . '</p>';
	}
	echo '<p style="margin:.6em 0;color:#646970;font-size:12px">' . esc_html( ( 'tool' === $lead->source ? 'Tool' : 'Contact' ) . ' · ' . mysql2date( 'Y-m-d H:i', $lead->created ) . ' · IP ' . $lead->ip ) . '</p>';
	if ( $lead->need ) {
		echo '<p><strong>Need:</strong> ' . esc_html( $lead->need ) . '</p>';
	}
	if ( $lead->message ) {
		echo '<p><strong>Message</strong></p><div style="background:#f6f7f7;border-radius:6px;padding:10px;white-space:pre-wrap">' . esc_html( $lead->message ) . '</div>';
	}
	echo '<p style="margin-top:14px"><a class="button button-primary" href="' . esc_attr( $reply ) . '">Reply by email</a></p>';

	$cur = isset( $lead->status ) && $lead->status ? $lead->status : 'new';
	echo '<form method="post" style="margin-top:16px;border-top:1px solid #f0f0f1;padding-top:14px">';
	wp_nonce_field( 'gsley_save_lead_' . $id );
	echo '<p><label for="lead_status"><strong>Follow-up status</strong></label><br><select id="lead_status" name="lead_status">';
	foreach ( array( 'new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'won' => 'Won', 'lost' => 'Lost' ) as $k => $v ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
	}
	echo '</select></p>';
	echo '<p><label for="lead_notes"><strong>Notes</strong></label><br><textarea id="lead_notes" name="lead_notes" rows="4" class="large-text">' . esc_textarea( (string) $lead->notes ) . '</textarea></p>';
	echo '<p><button class="button button-primary" name="gsley_save_lead" value="1">Save</button></p>';
	echo '</form></div>';

	// Right: what they produced.
	echo '<div style="flex:2 1 420px">';
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $subs WHERE lead_id = %d ORDER BY created DESC", $id ) ); // phpcs:ignore WordPress.DB
	if ( $rows ) {
		foreach ( $rows as $s ) {
			echo gsley_format_result( $s ); // phpcs:ignore WordPress.Security.EscapeOutput -- built with escaping inside.
		}
	} elseif ( 'tool' === $lead->source ) {
		echo '<p>No stored result for this submission.</p>';
	} else {
		echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px"><p>This is a contact enquiry (no diagnostic result).</p></div>';
	}
	echo '</div></div></div>';
}

/**
 * Render exactly what a visitor produced for one submission.
 *
 * @param object $s Submission row.
 * @return string Escaped HTML.
 */
function gsley_format_result( $s ) {
	$configs = gsley_diagnostics_configs();
	$cfg     = isset( $configs[ $s->tool ] ) ? $configs[ $s->tool ] : null;
	$title   = $cfg ? $cfg['title'] : $s->tool;
	$engine  = $cfg ? $cfg['engine'] : 'likert';
	$r       = json_decode( (string) $s->result, true );
	if ( ! is_array( $r ) ) {
		$r = array();
	}
	$scale_levels = array( 1 => 'Low', 2 => 'Medium', 3 => 'High' );

	$out  = '<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;margin-bottom:16px">';
	$out .= '<h2 style="margin-top:0">' . esc_html( $title ) . '</h2>';
	$dcol = 'sent' === $s->delivery_status ? '#15803D' : ( 'failed' === $s->delivery_status ? '#C0392B' : '#8c8f94' );
	$out .= '<p style="color:#646970;font-size:12px;margin-top:-6px">' . esc_html( mysql2date( 'Y-m-d H:i', $s->created ) ) . ' · report email: <span style="color:' . esc_attr( $dcol ) . '">' . esc_html( $s->delivery_status ) . '</span></p>';

	if ( 'likert' === $engine ) {
		$dimlabels = array();
		if ( $cfg ) {
			foreach ( $cfg['dimensions'] as $d ) {
				$dimlabels[ $d['id'] ] = $d['label'];
			}
		}
		$prio = isset( $r['priority'] ) ? ( isset( $dimlabels[ $r['priority'] ] ) ? $dimlabels[ $r['priority'] ] : $r['priority'] ) : '';
		$out .= '<p style="font-size:22px;font-weight:600;color:#0F1B2D;margin:.2em 0">' . (int) ( $r['overall'] ?? 0 ) . '<span style="font-size:13px;color:#646970">/100</span> &nbsp; ' . esc_html( $r['band'] ?? '' ) . '</p>';
		if ( $prio ) {
			$out .= '<p><strong>Priority:</strong> ' . esc_html( $prio ) . '</p>';
		}
		if ( ! empty( $r['dimensions'] ) && is_array( $r['dimensions'] ) ) {
			$out .= '<table class="widefat striped" style="margin:10px 0"><tbody>';
			foreach ( $r['dimensions'] as $d ) {
				$lab  = isset( $dimlabels[ $d['id'] ] ) ? $dimlabels[ $d['id'] ] : $d['id'];
				$out .= '<tr><td>' . esc_html( $lab ) . '</td><td style="text-align:right;font-family:monospace">' . (int) $d['pct'] . '%</td></tr>';
			}
			$out .= '</tbody></table>';
		}
		// Exactly what they answered.
		if ( $cfg && ! empty( $r['answers'] ) && is_array( $r['answers'] ) ) {
			$out .= '<details><summary style="cursor:pointer;font-weight:600">Their answers</summary><table class="widefat striped" style="margin:10px 0"><tbody>';
			foreach ( $cfg['questions'] as $i => $q ) {
				$val = isset( $r['answers'][ $i ] ) ? (int) $r['answers'][ $i ] : null;
				$lbl = ( null !== $val && isset( $cfg['scale'][ $val ] ) ) ? $cfg['scale'][ $val ]['label'] : '—';
				$out .= '<tr><td>' . esc_html( $q['text'] ) . '</td><td style="white-space:nowrap"><strong>' . esc_html( $lbl ) . '</strong></td></tr>';
			}
			$out .= '</tbody></table></details>';
		}
	} elseif ( 'calculator' === $engine ) {
		$out .= '<p style="font-size:22px;font-weight:600;color:#0F1B2D">≈ ₦' . number_format_i18n( (float) ( $r['total'] ?? 0 ) ) . '<span style="font-size:13px;color:#646970"> est. annual leakage</span></p>';
		$complabels = array( 'conversion' => 'Conversion leak', 'retention' => 'Retention leak', 'pricing' => 'Pricing leak', 'expansion' => 'Expansion gap' );
		if ( ! empty( $r['components'] ) ) {
			$out .= '<table class="widefat striped" style="margin:10px 0"><tbody>';
			foreach ( $r['components'] as $c ) {
				$lab  = isset( $complabels[ $c['id'] ] ) ? $complabels[ $c['id'] ] : $c['id'];
				$out .= '<tr><td>' . esc_html( $lab ) . '</td><td style="text-align:right;font-family:monospace">₦' . number_format_i18n( (float) $c['amount'] ) . '</td></tr>';
			}
			$out .= '</tbody></table>';
		}
		if ( $cfg && ! empty( $r['inputs'] ) && is_array( $r['inputs'] ) ) {
			$inlabels = array();
			foreach ( $cfg['inputs'] as $in ) {
				$inlabels[ $in['id'] ] = $in['label'];
			}
			$out .= '<details><summary style="cursor:pointer;font-weight:600">Their inputs</summary><table class="widefat striped" style="margin:10px 0"><tbody>';
			foreach ( $r['inputs'] as $k => $v ) {
				$lab  = isset( $inlabels[ $k ] ) ? $inlabels[ $k ] : $k;
				$out .= '<tr><td>' . esc_html( $lab ) . '</td><td style="text-align:right;font-family:monospace">' . esc_html( (string) $v ) . '</td></tr>';
			}
			$out .= '</tbody></table></details>';
		}
	} else { // finder
		$arealabels = array();
		if ( $cfg ) {
			foreach ( $cfg['areas'] as $a ) {
				$arealabels[ $a['id'] ] = $a['label'];
			}
		}
		if ( ! empty( $r['selections'] ) && is_array( $r['selections'] ) ) {
			$out .= '<p><strong>Highest-return opportunities they selected</strong></p><table class="widefat striped" style="margin:10px 0"><thead><tr><th>Workflow</th><th>Frequency</th><th>Pain</th></tr></thead><tbody>';
			foreach ( $r['selections'] as $sel ) {
				$lab  = isset( $arealabels[ $sel['area'] ] ) ? $arealabels[ $sel['area'] ] : $sel['area'];
				$out .= '<tr><td>' . esc_html( $lab ) . '</td><td>' . esc_html( $scale_levels[ (int) $sel['freq'] ] ?? '—' ) . '</td><td>' . esc_html( $scale_levels[ (int) $sel['pain'] ] ?? '—' ) . '</td></tr>';
			}
			$out .= '</tbody></table>';
		}
	}

	$out .= '</div>';
	return $out;
}

/**
 * Export all leads (with their latest result summary) as CSV.
 */
add_action( 'admin_post_gsley_export_leads', 'gsley_export_leads_csv' );
function gsley_export_leads_csv() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gsley_export_leads' ) ) {
		wp_die( 'Not allowed.' );
	}
	global $wpdb;
	$leads = gsley_table_leads();
	$subs  = gsley_table_submissions();
	$rows  = $wpdb->get_results( "SELECT * FROM $leads ORDER BY created DESC" ); // phpcs:ignore WordPress.DB

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=gsley-leads-' . gmdate( 'Ymd-His' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Created', 'Source', 'Status', 'Name', 'Email', 'Organisation', 'Need', 'Tool', 'Overall', 'Band', 'Priority', 'Report delivery', 'Notes' ) );
	foreach ( $rows as $l ) {
		$sub = null;
		if ( 'tool' === $l->source ) {
			$sub = $wpdb->get_row( $wpdb->prepare( "SELECT overall, band, priority, delivery_status FROM $subs WHERE lead_id = %d ORDER BY created DESC LIMIT 1", $l->id ) ); // phpcs:ignore WordPress.DB
		}
		fputcsv(
			$out,
			array(
				$l->created, $l->source, $l->status, $l->name, $l->email, $l->organisation, $l->need, $l->tool,
				$sub->overall ?? '', $sub->band ?? '', $sub->priority ?? '', $l->delivery_status, $l->notes,
			)
		);
	}
	fclose( $out );
	exit;
}
