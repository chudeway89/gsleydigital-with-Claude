<?php
/**
 * Plugin Name:       GSLEY Diagnostics
 * Plugin URI:        https://gsleydigital.com/tools
 * Description:       The eight GSLEY interactive diagnostic tools, each as an editor block. Answer-first; one shared shell + engine (Likert) plus two variant engines (calculator, finder). Lead capture, persistence, and Resend delivery hook the gsley_diagnostics_submission action. Application logic lives here, never in the theme.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            GSLEY Digital
 * License:           GPL-2.0-or-later
 * Text Domain:       gsley-diagnostics
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GSLEY_DIAGNOSTICS_VER', '0.1.0' );
define( 'GSLEY_DIAGNOSTICS_DIR', plugin_dir_path( __FILE__ ) );
define( 'GSLEY_DIAGNOSTICS_URL', plugin_dir_url( __FILE__ ) );

require_once GSLEY_DIAGNOSTICS_DIR . 'includes/configs.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/render.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/rest.php';

// Backend: database, email, persistence, dashboard.
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/db.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/resend.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/leads.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/report.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/submissions.php';
require_once GSLEY_DIAGNOSTICS_DIR . 'includes/contact.php';
if ( is_admin() ) {
	require_once GSLEY_DIAGNOSTICS_DIR . 'includes/admin.php';
	require_once GSLEY_DIAGNOSTICS_DIR . 'includes/setup.php';
}

// Create the custom tables on activation.
register_activation_hook( __FILE__, 'gsley_install_tables' );

/**
 * Register front-end assets (enqueued on demand by the render callback).
 */
function gsley_diagnostics_register_assets() {
	wp_register_style(
		'gsley-diagnostics',
		GSLEY_DIAGNOSTICS_URL . 'assets/tool.css',
		array(),
		GSLEY_DIAGNOSTICS_VER
	);
	wp_register_script(
		'gsley-diagnostics',
		GSLEY_DIAGNOSTICS_URL . 'assets/tool.js',
		array(),
		GSLEY_DIAGNOSTICS_VER,
		true
	);
	wp_localize_script(
		'gsley-diagnostics',
		'gsleyDiagnostics',
		array(
			'rest'  => esc_url_raw( rest_url( 'gsley/v1/tools/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		)
	);
}
add_action( 'init', 'gsley_diagnostics_register_assets' );

/**
 * Register one dynamic block per tool, all sharing the render callback.
 */
function gsley_diagnostics_register_blocks() {
	$configs = gsley_diagnostics_configs();
	foreach ( $configs as $slug => $cfg ) {
		register_block_type(
			'gsley/tool-' . $slug,
			array(
				'api_version'     => 2,
				'title'           => $cfg['title'],
				'category'        => 'gsley',
				'attributes'      => array(
					'tool' => array( 'type' => 'string', 'default' => $slug ),
				),
				'render_callback' => 'gsley_diagnostics_render',
				'editor_script'   => 'gsley-diagnostics-editor',
				'supports'        => array( 'html' => false ),
			)
		);
	}
}
add_action( 'init', 'gsley_diagnostics_register_blocks' );

/**
 * Editor script + the block list it needs to register the inserter entries.
 */
function gsley_diagnostics_enqueue_editor() {
	wp_register_script(
		'gsley-diagnostics-editor',
		GSLEY_DIAGNOSTICS_URL . 'assets/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
		GSLEY_DIAGNOSTICS_VER,
		true
	);

	$configs = gsley_diagnostics_configs();
	$icons   = array(
		'revenue-leakage-calculator'   => 'calculator',
		'automation-opportunity-finder' => 'search',
	);
	$blocks = array();
	foreach ( $configs as $slug => $cfg ) {
		$blocks[] = array(
			'name'        => 'gsley/tool-' . $slug,
			'tool'        => $slug,
			'title'       => $cfg['title'],
			'code'        => isset( $cfg['code'] ) ? $cfg['code'] : '',
			'description' => 'GSLEY ' . ( 'calculator' === $cfg['engine'] ? 'calculator' : ( 'finder' === $cfg['engine'] ? 'finder' : 'diagnostic' ) ) . ' — answer-first, emails the full report.',
			'icon'        => isset( $icons[ $slug ] ) ? $icons[ $slug ] : 'chart-bar',
		);
	}
	wp_add_inline_script(
		'gsley-diagnostics-editor',
		'window.gsleyDiagnosticsBlocks = ' . wp_json_encode( array( 'blocks' => $blocks ) ) . ';',
		'before'
	);

	wp_enqueue_script( 'gsley-diagnostics-editor' );
	// Show the on-brand tool styles in the editor placeholder context too.
	wp_enqueue_style( 'gsley-diagnostics' );
}
add_action( 'enqueue_block_editor_assets', 'gsley_diagnostics_enqueue_editor' );

/**
 * Add a "GSLEY" block category.
 */
function gsley_diagnostics_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'gsley',
			'title' => __( 'GSLEY Diagnostics', 'gsley-diagnostics' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'gsley_diagnostics_block_category' );
