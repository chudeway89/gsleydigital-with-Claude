<?php
/**
 * GSLEY Security — Settings → GSLEY Security: options, status checks, event log.
 *
 * @package GSLEY_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_options_page( 'GSLEY Security', 'GSLEY Security', 'manage_options', 'gsley-security', 'gsley_sec_render_admin' );
} );

add_action( 'admin_init', function () {
	register_setting( 'gsley_security', 'gsley_security', array( 'type' => 'array', 'sanitize_callback' => 'gsley_sec_sanitize' ) );
} );

function gsley_sec_sanitize( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$out = array();
	foreach ( array( 'csp_enforce', 'hsts', 'hsts_subdomains', 'hsts_preload', 'require_2fa' ) as $k ) {
		$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
	}
	$out['csp_extra']       = sanitize_textarea_field( isset( $in['csp_extra'] ) ? $in['csp_extra'] : '' );
	$out['alert_email']     = sanitize_email( isset( $in['alert_email'] ) ? $in['alert_email'] : '' );
	$out['admin_session_h'] = max( 1, min( 48, (int) ( isset( $in['admin_session_h'] ) ? $in['admin_session_h'] : 8 ) ) );
	gsley_sec_log( 'security_settings_changed', array( 'detail' => 'csp_enforce=' . $out['csp_enforce'] . ' require_2fa=' . $out['require_2fa'] ), true );
	return $out;
}

/** Quick configuration checks shown on the settings page. */
function gsley_sec_checks() {
	$checks   = array();
	$checks[] = array( is_ssl(), 'Admin is served over HTTPS' );
	$checks[] = array( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG && ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY ) ), 'PHP errors are not displayed to visitors (WP_DEBUG_DISPLAY off)' );
	$checks[] = array( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT, 'Theme/plugin file editor disabled' );
	$checks[] = array( ! get_option( 'users_can_register' ) || 'subscriber' === get_option( 'default_role' ), 'Open registration is off (or new users are Subscribers)' );
	$checks[] = array( defined( 'GSLEY_PAYSTACK_SECRET_KEY' ) || '' === (string) get_option( 'gsley_paystack_secret', '' ), 'Paystack secret kept in wp-config.php, not the database' );
	$checks[] = array( defined( 'GSLEY_RESEND_API_KEY' ) || '' === (string) get_option( 'gsley_resend_api_key', '' ), 'Resend key kept in wp-config.php, not the database' );
	$admins   = get_users( array( 'role' => 'administrator', 'fields' => array( 'ID', 'user_login' ) ) );
	foreach ( $admins as $a ) {
		$checks[] = array( gsley_2fa_enabled( $a->ID ), 'Two-factor enabled for administrator “' . $a->user_login . '”' );
		$checks[] = array( ! in_array( strtolower( $a->user_login ), array( 'admin', 'administrator', 'root' ), true ), 'Administrator “' . $a->user_login . '” has a non-default username' );
	}
	$core = get_site_transient( 'update_core' );
	$checks[] = array( ! ( $core && ! empty( $core->updates[0]->response ) && 'upgrade' === $core->updates[0]->response ), 'WordPress core is up to date' );
	$plugins = get_site_transient( 'update_plugins' );
	$checks[] = array( empty( $plugins->response ), 'All plugins are up to date' . ( ! empty( $plugins->response ) ? ' (' . count( $plugins->response ) . ' need updating)' : '' ) );
	return $checks;
}

function gsley_sec_render_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$s = gsley_sec_settings();
	echo '<div class="wrap"><h1>GSLEY Security</h1>';

	echo '<h2>Status</h2><table class="widefat striped" style="max-width:900px"><tbody>';
	foreach ( gsley_sec_checks() as $c ) {
		echo '<tr><td style="width:28px;font-size:16px">' . ( $c[0] ? '<span style="color:#15803D">✓</span>' : '<span style="color:#B42318">✗</span>' ) . '</td><td>' . esc_html( $c[1] ) . '</td></tr>';
	}
	echo '</tbody></table>';

	echo '<form method="post" action="options.php">';
	settings_fields( 'gsley_security' );
	$cb = function ( $key, $label, $help = '' ) use ( $s ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><label><input type="checkbox" name="gsley_security[' . esc_attr( $key ) . ']" value="1"' . checked( 1, (int) $s[ $key ], false ) . '> On</label>' . ( $help ? '<p class="description">' . wp_kses_post( $help ) . '</p>' : '' ) . '</td></tr>';
	};
	echo '<h2>Settings</h2><table class="form-table" role="presentation"><tbody>';
	$cb( 'require_2fa', 'Require two-factor for administrators', 'Administrators without two-factor are sent to their profile to set it up. Emergency bypass: <code>define( \'GSLEY_SECURITY_DISABLE_2FA\', true );</code> in wp-config.php.' );
	$cb( 'csp_enforce', 'Enforce the full Content-Security-Policy', 'Off = report-only (violations appear in the browser console as warnings). Turn on after browsing the site with the console open and seeing no CSP warnings. A baseline policy (clickjacking, base-uri, plugins, form targets) is always enforced.' );
	echo '<tr><th scope="row"><label for="gsley_csp_extra">Extra allowed hosts</label></th><td><textarea id="gsley_csp_extra" name="gsley_security[csp_extra]" rows="3" class="large-text code">' . esc_textarea( $s['csp_extra'] ) . '</textarea><p class="description">One host per line (e.g. <code>cdn.example.com</code>), if a CSP warning shows a legitimate source being blocked.</p></td></tr>';
	$cb( 'hsts', 'Send HSTS (1 year)', 'Browsers will only use HTTPS for this site.' );
	$cb( 'hsts_subdomains', 'HSTS: include subdomains', 'Only turn on once <em>every</em> subdomain (mail., www., staging. …) serves valid HTTPS.' );
	$cb( 'hsts_preload', 'HSTS: preload', 'Needs “include subdomains”. Then submit the domain at hstspreload.org. Hard to undo — browsers ship the list.' );
	echo '<tr><th scope="row"><label for="gsley_session_h">Administrator session length</label></th><td><input type="number" min="1" max="48" id="gsley_session_h" name="gsley_security[admin_session_h]" value="' . esc_attr( (int) $s['admin_session_h'] ) . '" class="small-text"> hours <p class="description">Without “Remember me”. With it, administrators stay signed in for 3 days.</p></td></tr>';
	echo '<tr><th scope="row"><label for="gsley_alert_email">Alert email</label></th><td><input type="email" id="gsley_alert_email" name="gsley_security[alert_email]" value="' . esc_attr( $s['alert_email'] ) . '" class="regular-text" placeholder="' . esc_attr( get_option( 'admin_email' ) ) . '"><p class="description">Lockouts, new administrators, 2FA changes, payment/webhook anomalies, secret changes and lead exports are emailed here (at most one email per event type per hour).</p></td></tr>';
	echo '</tbody></table>';
	submit_button();
	echo '</form>';

	$t    = gsley_sec_table();
	$rows = $wpdb->get_results( "SELECT * FROM $t ORDER BY id DESC LIMIT 200" ); // phpcs:ignore WordPress.DB
	echo '<h2>Recent security events</h2><p class="description">Last 200 events (kept 90 days). Times are UTC. A copy of every event is also written to the server error log.</p>';
	echo '<table class="widefat striped"><thead><tr><th>When (UTC)</th><th>Event</th><th>User</th><th>IP</th><th>Path</th><th>Detail</th></tr></thead><tbody>';
	foreach ( (array) $rows as $r ) {
		echo '<tr><td>' . esc_html( $r->created ) . '</td><td><code>' . esc_html( $r->event ) . '</code></td><td>' . esc_html( $r->user_login ) . '</td><td>' . esc_html( $r->ip ) . '</td><td>' . esc_html( $r->path ) . '</td><td>' . esc_html( $r->detail ) . '</td></tr>';
	}
	if ( ! $rows ) {
		echo '<tr><td colspan="6">No events yet.</td></tr>';
	}
	echo '</tbody></table></div>';
}
