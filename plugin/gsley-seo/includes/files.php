<?php
/**
 * GSLEY SEO — robots.txt, sitemap.xml, llms.txt.
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * robots.txt — allow search + named AI crawlers; point to the sitemap.
 * (phase1-handoff.md §5.)
 */
add_filter( 'robots_txt', 'gsley_seo_robots_txt', 10, 2 );
function gsley_seo_robots_txt( $output, $public ) {
	$sitemap = home_url( '/sitemap.xml' );
	$lines   = array(
		'User-agent: *',
		'Allow: /',
		'',
		'User-agent: GPTBot',
		'Allow: /',
		'',
		'User-agent: ClaudeBot',
		'Allow: /',
		'',
		'User-agent: PerplexityBot',
		'Allow: /',
		'',
		'Sitemap: ' . $sitemap,
		'',
	);
	$txt = implode( "\n", $lines );
	if ( ! $public ) {
		// Site is set to discourage indexing (e.g. staging): respect that.
		$txt = "User-agent: *\nDisallow: /\n\nSitemap: " . $sitemap . "\n";
	}
	return $txt;
}

/**
 * Serve /sitemap.xml and /llms.txt directly (before WP routes to a 404).
 */
add_action( 'init', 'gsley_seo_serve_files', 0 );
function gsley_seo_serve_files() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$path = trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' );

	if ( 'sitemap.xml' === $path ) {
		gsley_seo_output_sitemap();
	}
	if ( 'llms.txt' === $path ) {
		$file = GSLEY_SEO_DIR . 'llms.txt';
		if ( file_exists( $file ) ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo file_get_contents( $file ); // phpcs:ignore
			exit;
		}
	}
}

/**
 * Build the sitemap from the built URL set with handoff §5 priorities;
 * redirected and unbuilt URLs are excluded.
 */
function gsley_seo_output_sitemap() {
	$urls = array();
	$urls[] = array( home_url( '/' ), '1.0', 'weekly' );
	$urls[] = array( home_url( '/about' ), '0.6', 'monthly' );
	$urls[] = array( home_url( '/contact' ), '0.6', 'monthly' );
	$urls[] = array( home_url( '/services' ), '0.8', 'monthly' );
	$urls[] = array( home_url( '/services/market' ), '0.8', 'monthly' );
	$urls[] = array( home_url( '/services/digital' ), '0.8', 'monthly' );
	$urls[] = array( home_url( '/tools' ), '0.8', 'monthly' );

	foreach ( gsley_seo_market_services() as $slug => $name ) {
		$urls[] = array( home_url( '/services/market/' . $slug ), '0.7', 'monthly' );
	}
	foreach ( gsley_seo_digital_services() as $slug => $name ) {
		$urls[] = array( home_url( '/services/digital/' . $slug ), '0.7', 'monthly' );
	}
	foreach ( gsley_seo_tools() as $slug => $name ) {
		$urls[] = array( home_url( '/tools/' . $slug ), '0.7', 'monthly' );
	}
	foreach ( gsley_seo_legal() as $slug => $name ) {
		$urls[] = array( home_url( '/legal/' . $slug ), '0.3', 'yearly' );
	}

	$urls = apply_filters( 'gsley_seo_sitemap_urls', $urls );

	header( 'Content-Type: application/xml; charset=utf-8' );
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $urls as $u ) {
		echo "  <url>\n";
		echo '    <loc>' . esc_url( $u[0] ) . "</loc>\n";
		echo '    <changefreq>' . esc_html( $u[2] ) . "</changefreq>\n";
		echo '    <priority>' . esc_html( $u[1] ) . "</priority>\n";
		echo "  </url>\n";
	}
	echo '</urlset>';
	exit;
}
