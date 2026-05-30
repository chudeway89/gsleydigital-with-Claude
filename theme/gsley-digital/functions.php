<?php
/**
 * GSLEY Digital — block theme setup.
 *
 * Design tokens are defined in theme.json. This file only wires up the
 * supplemental stylesheet (for what theme.json cannot express) and a few
 * theme supports. Tools, lead capture, email and the admin dashboard live
 * in a SEPARATE plugin — never add application logic here.
 *
 * @package GSLEY_Digital
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme supports for Full Site Editing.
 */
function gsley_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'style', 'script', 'navigation-widgets' ) );

	// Load the component stylesheet inside the block editor too, so locked
	// patterns render on-brand in the Site Editor. editor.css neutralises
	// front-end-only behaviours (scroll-reveal, sticky header) for editing.
	add_editor_style( array( 'assets/css/gsley.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'gsley_setup' );

/**
 * Register the GSLEY pattern category so the theme's block patterns group
 * together in the editor inserter. Pattern files live in /patterns and are
 * auto-registered by WordPress from their header comments.
 */
function gsley_register_pattern_category() {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category(
			'gsley',
			array(
				'label'       => __( 'GSLEY Digital', 'gsley-digital' ),
				'description' => __( 'On-brand, structure-locked sections for the GSLEY Digital site.', 'gsley-digital' ),
			)
		);
	}
}
add_action( 'init', 'gsley_register_pattern_category' );

/**
 * Enqueue the supplemental component stylesheet on the front end.
 *
 * theme.json carries the design tokens (colour, type, spacing, layout).
 * gsley.css carries only the components those tokens cannot express:
 * the sticky/blur header, nav, footer grid, buttons, section helpers,
 * the WhatsApp FAB and the scroll-reveal effect.
 */
function gsley_enqueue_assets() {
	$style_path = get_theme_file_path( 'assets/css/gsley.css' );
	$version    = file_exists( $style_path ) ? filemtime( $style_path ) : '0.1.0';

	wp_enqueue_style(
		'gsley-components',
		get_theme_file_uri( 'assets/css/gsley.css' ),
		array(),
		$version
	);

	$script_path = get_theme_file_path( 'assets/js/gsley.js' );
	$script_ver  = file_exists( $script_path ) ? filemtime( $script_path ) : '0.1.0';

	wp_enqueue_script(
		'gsley-behaviour',
		get_theme_file_uri( 'assets/js/gsley.js' ),
		array(),
		$script_ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'gsley_enqueue_assets' );

/**
 * Preload the above-the-fold display font weights for performance,
 * per phase1-foundation.md §3.2 (preload 400 + 600 display weights).
 */
function gsley_preload_fonts() {
	$fonts = array(
		'assets/fonts/source-serif-4-400-latin.woff2',
		'assets/fonts/source-serif-4-600-latin.woff2',
		'assets/fonts/inter-400-latin.woff2',
	);
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( $font ) )
		);
	}
}
add_action( 'wp_head', 'gsley_preload_fonts', 1 );
