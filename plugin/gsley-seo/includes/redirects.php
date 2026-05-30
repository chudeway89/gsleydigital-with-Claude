<?php
/**
 * GSLEY SEO — 301 redirects (phase1-handoff.md §4 + foundation §2).
 *
 * Note: /services/market/social-media-management is intentionally NOT
 * redirected — it is a separate page (decision), so it must resolve, not 301.
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @return array<string,string> source path (no slashes) => target path/url */
function gsley_seo_redirect_map() {
	return array(
		'diagnostic'                            => '/tools/growth-diagnostic',
		'services/market/media-management'      => '/services/market/institutional-brand-and-reputation#media-management',
		'services/digital/business-development' => '/services/market/strategic-partnerships',
	);
}

add_action( 'template_redirect', 'gsley_seo_do_redirects', 1 );
function gsley_seo_do_redirects() {
	$path = gsley_seo_current_path();
	if ( '' === $path ) {
		return;
	}
	$map = gsley_seo_redirect_map();
	if ( isset( $map[ $path ] ) ) {
		$target = $map[ $path ];
		// Relative targets resolve against the site root; keep any #fragment.
		if ( '/' === substr( $target, 0, 1 ) ) {
			$target = home_url( $target );
		}
		wp_redirect( $target, 301 );
		exit;
	}
}
