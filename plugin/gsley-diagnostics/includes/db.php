<?php
/**
 * GSLEY Diagnostics — database layer.
 *
 * Two custom tables: gsley_leads (one row per capture event — contact or tool)
 * and gsley_tool_submissions (one row per completed diagnostic, linked to a
 * lead). Delivery status for the Resend send is logged on each row.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GSLEY_DIAGNOSTICS_DB_VER', '1.2.0' );

function gsley_table_leads() {
	global $wpdb;
	return $wpdb->prefix . 'gsley_leads';
}

function gsley_table_submissions() {
	global $wpdb;
	return $wpdb->prefix . 'gsley_tool_submissions';
}

/**
 * Create / update the custom tables. Safe to run repeatedly (dbDelta).
 */
function gsley_install_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$leads   = gsley_table_leads();
	$subs    = gsley_table_submissions();

	$sql_leads = "CREATE TABLE $leads (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		source varchar(20) NOT NULL DEFAULT 'contact',
		name varchar(190) NOT NULL DEFAULT '',
		email varchar(190) NOT NULL DEFAULT '',
		organisation varchar(190) NOT NULL DEFAULT '',
		need varchar(190) NOT NULL DEFAULT '',
		message text NULL,
		tool varchar(100) NOT NULL DEFAULT '',
		ip varchar(64) NOT NULL DEFAULT '',
		user_agent varchar(255) NOT NULL DEFAULT '',
		delivery_status varchar(20) NOT NULL DEFAULT 'pending',
		delivery_id varchar(190) NOT NULL DEFAULT '',
		delivery_error text NULL,
		status varchar(20) NOT NULL DEFAULT 'new',
		notes text NULL,
		PRIMARY KEY  (id),
		KEY email (email),
		KEY created (created),
		KEY source (source),
		KEY status (status)
	) $charset;";

	$sql_subs = "CREATE TABLE $subs (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		lead_id bigint(20) unsigned NOT NULL DEFAULT 0,
		created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		tool varchar(100) NOT NULL DEFAULT '',
		overall int(11) NULL,
		band varchar(100) NOT NULL DEFAULT '',
		priority varchar(100) NOT NULL DEFAULT '',
		result longtext NULL,
		delivery_status varchar(20) NOT NULL DEFAULT 'pending',
		delivery_id varchar(190) NOT NULL DEFAULT '',
		delivery_error text NULL,
		delivered_at datetime NULL,
		amount int(11) NOT NULL DEFAULT 0,
		currency varchar(8) NOT NULL DEFAULT '',
		paid tinyint(1) NOT NULL DEFAULT 0,
		payment_ref varchar(190) NOT NULL DEFAULT '',
		token varchar(64) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY lead_id (lead_id),
		KEY tool (tool),
		KEY created (created),
		KEY payment_ref (payment_ref)
	) $charset;";

	dbDelta( $sql_leads );
	dbDelta( $sql_subs );
	update_option( 'gsley_diagnostics_db_ver', GSLEY_DIAGNOSTICS_DB_VER );
}

/**
 * Run the installer on upgrade (activation hook covers fresh installs).
 */
function gsley_maybe_upgrade_db() {
	if ( get_option( 'gsley_diagnostics_db_ver' ) !== GSLEY_DIAGNOSTICS_DB_VER ) {
		gsley_install_tables();
	}
}
add_action( 'admin_init', 'gsley_maybe_upgrade_db' );
