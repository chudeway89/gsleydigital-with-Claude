<?php
/**
 * Plugin Name:       GSLEY SEO & AI-Search
 * Plugin URI:        https://gsleydigital.com
 * Description:       The GSLEY SEO/AI-search layer: JSON-LD schema, per-page Open Graph images, sitemap.xml, robots.txt (search + AI crawlers), llms.txt, and the legacy-URL 301 redirects. Kept in a plugin so it survives theme changes.
 * Version:           0.2.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            GSLEY Digital
 * License:           GPL-2.0-or-later
 * Text Domain:       gsley-seo
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GSLEY_SEO_VER', '0.2.1' );
define( 'GSLEY_SEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'GSLEY_SEO_URL', plugin_dir_url( __FILE__ ) );

require_once GSLEY_SEO_DIR . 'includes/data.php';
require_once GSLEY_SEO_DIR . 'includes/redirects.php';
require_once GSLEY_SEO_DIR . 'includes/files.php';
require_once GSLEY_SEO_DIR . 'includes/meta.php';
require_once GSLEY_SEO_DIR . 'includes/schema.php';

/**
 * Flush rewrite rules note: this plugin serves /sitemap.xml and /llms.txt by
 * intercepting the request on `init`, so no custom rewrite rules are needed.
 * robots.txt uses the core `robots_txt` filter. Nothing to flush on activation.
 */
