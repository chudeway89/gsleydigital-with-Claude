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
	$title     = gsley_seo_doc_title();
	$og_type   = ( 'home' === $pt['type'] ) ? 'website' : 'website';
	$og_image  = gsley_seo_og_base() . '/' . gsley_seo_og_slug( $pt ) . '.png';

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
