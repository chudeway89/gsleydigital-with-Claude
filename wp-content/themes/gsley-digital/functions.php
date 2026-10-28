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
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);
}
add_action( 'wp_enqueue_scripts', 'gsley_enqueue_assets' );

/**
 * WooCommerce is installed on the site but this theme sells nothing through it
 * (paid audits use Paystack). It was loading 5 stylesheets and 6 scripts on
 * every page. Keep them only on actual WooCommerce pages.
 */
function gsley_trim_woocommerce_assets() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	if ( ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
		|| ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() ) ) {
		return;
	}
	foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-blocktheme', 'wc-blocks-style' ) as $h ) {
		wp_dequeue_style( $h );
	}
	foreach ( array( 'wc-add-to-cart', 'woocommerce', 'wc-order-attribution', 'sourcebuster-js', 'js-cookie', 'jquery-blockui', 'wc-cart-fragments' ) as $h ) {
		wp_dequeue_script( $h );
	}
}
add_action( 'wp_enqueue_scripts', 'gsley_trim_woocommerce_assets', 99 );

/**
 * Preload the above-the-fold display font weights for performance,
 * per phase1-foundation.md §3.2 (preload 400 + 600 display weights).
 */
function gsley_preload_fonts() {
	$fonts = array(
		'assets/fonts/source-serif-4-var-latin.woff2', // variable: weight 400–600 + optical size
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

/**
 * One skip link. The header part carries the theme's .skip-link; WordPress
 * core adds a second one for block themes, which screen readers announce twice.
 */
function gsley_remove_core_skip_link() {
	remove_action( 'wp_footer', 'the_block_template_skip_link' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
}
add_action( 'after_setup_theme', 'gsley_remove_core_skip_link' );

/**
 * `no-js` on <html>, removed by an inline script in <head>. CSS uses it so
 * scroll-reveal content can never stay hidden if JavaScript fails.
 */
function gsley_no_js_class( $output ) {
	return is_admin() ? $output : $output . ' class="no-js"';
}
add_filter( 'language_attributes', 'gsley_no_js_class' );

function gsley_no_js_remover() {
	echo "<script>document.documentElement.classList.remove('no-js');</script>\n";
}
add_action( 'wp_head', 'gsley_no_js_remover', 0 );

/**
 * Fallbacks for the GSLEY plugin's shortcodes used in the header/footer parts.
 * Site facts and the services registry live in the GSLEY Diagnostics plugin;
 * if it is ever deactivated, these keep the footer from printing raw
 * [shortcodes] and keep the WhatsApp button working.
 */
function gsley_fallback_shortcodes() {
	$empty = function () {
		return '';
	};
	foreach ( array( 'gsley_socials', 'gsley_footer_services', 'gsley_service_options', 'gsley_booking', 'gsley_offices', 'gsley_count', 'gsley' ) as $tag ) {
		if ( ! shortcode_exists( $tag ) ) {
			add_shortcode( $tag, $empty );
		}
	}
	if ( ! shortcode_exists( 'gsley_actions' ) ) {
		add_shortcode(
			'gsley_actions',
			function () {
				return '<a class="wa-fab" href="https://wa.me/2349024767079" target="_blank" rel="noopener" aria-label="Chat with GSLEY on WhatsApp">WhatsApp</a>';
			}
		);
	}
}
add_action( 'init', 'gsley_fallback_shortcodes', 99 );
