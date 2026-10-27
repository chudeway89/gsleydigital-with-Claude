<?php
/**
 * GSLEY SEO — meta description, canonical, and Open Graph / Twitter tags.
 * Wires up the per-page OG images (phase1-handoff.md §5/§6).
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Base URL for the OG images. Bundled with the plugin; filterable. */
function gsley_seo_og_base() {
	return apply_filters( 'gsley_seo_og_base', rtrim( GSLEY_SEO_URL, '/' ) . '/assets/og' );
}

function gsley_seo_doc_title() {
	if ( function_exists( 'wp_get_document_title' ) ) {
		return wp_get_document_title();
	}
	return get_bloginfo( 'name' );
}

function gsley_seo_canonical( $pt ) {
	if ( 'home' === $pt['type'] ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		$p = get_permalink();
		if ( $p ) {
			return $p;
		}
	}
	$path = gsley_seo_current_path();
	return home_url( '/' . $path );
}

/** A meta description: page excerpt if set, else a type-appropriate default. */
function gsley_seo_description( $pt ) {
	$default = 'A productised growth and AI studio for institutions and ambitious scale-ups. Predictable demand and AI-native systems. Engineered, not opinionated.';
	$map = array(
		'home'           => $default,
		'about'          => 'A board-level commercial systems studio. Two pillars, one operating method, documented archetypes — never opinions.',
		'contact'        => 'Start a conversation with GSLEY Digital — by WhatsApp, a written brief, or a discovery call. Lagos-based, working worldwide.',
		'services-index' => 'Two pillars, one commercial system. GSLEY Market engineers demand and brand; GSLEY Digital builds AI-native operating systems.',
		'tools-hub'      => 'Eight free diagnostics, each ending in a scored read-out and one clear recommendation. Answer-first, no sign-up to begin.',
	);
	if ( isset( $map[ $pt['type'] ] ) ) {
		return $map[ $pt['type'] ];
	}
	if ( is_singular() ) {
		$ex = get_the_excerpt( get_queried_object_id() );
		if ( $ex ) {
			return wp_strip_all_tags( $ex );
		}
	}
	if ( 'service' === $pt['type'] ) {
		return $pt['name'] . ' — a productised engagement on a documented archetype, by GSLEY Digital.';
	}
	if ( 'pillar' === $pt['type'] ) {
		return ( 'market' === $pt['pillar'] ? 'GSLEY Market' : 'GSLEY Digital' ) . ' — the full service catalogue, productised on documented archetypes.';
	}
	if ( 'tool' === $pt['type'] ) {
		return 'A free ' . $pt['name'] . ' from GSLEY — answer-first, with an instant scored read-out and an emailed report.';
	}
	if ( 'legal' === $pt['type'] ) {
		return $pt['name'] . ' — GSLEY Digital.';
	}
	return $default;
}

add_action( 'wp_head', 'gsley_seo_output_meta', 1 );
function gsley_seo_output_meta() {
	if ( is_admin() ) {
		return;
	}
	$pt        = gsley_seo_page_type();
	$canonical = gsley_seo_canonical( $pt );
	$desc      = gsley_seo_description( $pt );
	$title     = gsley_seo_title( $pt );
	$og_type   = 'website';
	$og_image  = gsley_seo_og_base() . '/' . gsley_seo_og_slug( $pt ) . '.png';

	// Yoast present: it prints canonical/description/OG/Twitter text (fed with our
	// values via the filters below), so only add the share image it lacks.
	if ( gsley_seo_yoast_active() ) {
		echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n"
			. '<meta property="og:image:width" content="1200">' . "\n"
			. '<meta property="og:image:height" content="630">' . "\n"
			. '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}

	$tags  = '';
	$tags .= '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	$tags .= '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	$tags .= '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
	$tags .= '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	$tags .= '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	$tags .= '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
	$tags .= '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
	$tags .= '<meta property="og:site_name" content="GSLEY Digital">' . "\n";
	$tags .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
	$tags .= '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	$tags .= '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	$tags .= '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";

	echo $tags; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
}

/* ============================================================ TITLES + YOAST COEXISTENCE
   The live site also runs Yoast SEO. Two plugins printing canonicals, Open Graph
   and schema confused crawlers. Division of labour:
   - Yoast prints canonical / description / OG / Twitter text — fed our values.
   - This plugin prints the JSON-LD schema and per-page OG images.
   - Yoast's own schema graph is switched off so there is exactly one.
   - Titles follow the brief's pattern: "Page — GSLEY Market|Digital". */

function gsley_seo_yoast_active() {
	return defined( 'WPSEO_VERSION' );
}

/** Title in the brief's pattern, ≤ ~60 chars. */
function gsley_seo_title( $pt ) {
	switch ( $pt['type'] ) {
		case 'home':
			return 'GSLEY Digital — Engineered Precision';
		case 'service':
			return $pt['name'] . ' — GSLEY ' . ( 'market' === $pt['pillar'] ? 'Market' : 'Digital' );
		case 'pillar':
			return 'market' === $pt['pillar'] ? 'GSLEY Market — Demand, Brand & Commercial' : 'GSLEY Digital — AI-Native Systems';
		case 'services-index':
			return 'Services — GSLEY Digital';
		case 'tools-hub':
			return 'Free Diagnostics & Audits — GSLEY Digital';
		case 'tool':
		case 'legal':
			return $pt['name'] . ' — GSLEY Digital';
		case 'about':
			return 'About — GSLEY Digital';
		case 'contact':
			return 'Contact — GSLEY Digital';
	}
	$t = is_singular() ? single_post_title( '', false ) : '';
	return $t ? $t . ' — GSLEY Digital' : 'GSLEY Digital';
}

function gsley_seo_filter_title( $title = '' ) {
	if ( is_admin() || is_feed() ) {
		return $title;
	}
	return gsley_seo_title( gsley_seo_page_type() );
}
// Without Yoast: WordPress document title. With Yoast: Yoast's title.
add_filter( 'pre_get_document_title', 'gsley_seo_filter_title', 20 );
add_filter( 'wpseo_title', 'gsley_seo_filter_title', 20 );
add_filter( 'wpseo_opengraph_title', 'gsley_seo_filter_title', 20 );
add_filter( 'wpseo_twitter_title', 'gsley_seo_filter_title', 20 );

function gsley_seo_filter_desc( $desc = '' ) {
	if ( is_admin() ) {
		return $desc;
	}
	return gsley_seo_description( gsley_seo_page_type() );
}
add_filter( 'wpseo_metadesc', 'gsley_seo_filter_desc', 20 );
add_filter( 'wpseo_opengraph_desc', 'gsley_seo_filter_desc', 20 );
add_filter( 'wpseo_twitter_description', 'gsley_seo_filter_desc', 20 );

add_filter( 'wpseo_opengraph_site_name', function () { return 'GSLEY Digital'; }, 20 );

// One schema graph: ours (routing-aware, real FAQ extraction). Yoast's is off.
add_filter( 'wpseo_json_ld_output', '__return_false', 20 );
