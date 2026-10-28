<?php
/**
 * GSLEY Digital — page importer.
 *
 * Creates (or updates) the site's Pages from the theme's registered block
 * patterns, at the nested slugs in the routing map, and assigns the
 * full-width page template. Idempotent: re-running updates content in place
 * rather than duplicating pages.
 *
 * Run with WP-CLI, from the WordPress root, with the GSLEY Digital theme active:
 *
 *   wp eval-file wp-content/themes/gsley-digital/bin/import-pages.php
 *
 * Optional: set the homepage to a static front page afterwards if you prefer
 * a Page over the front-page.html template (the template already renders "/").
 *
 * @package GSLEY_Digital
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "This script must be run through WP-CLI: wp eval-file …\n" );
	exit( 1 );
}

/**
 * Pull a registered pattern's block markup by slug.
 */
function gsley_pattern_content( $slug ) {
	if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return '';
	}
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	return $pattern ? $pattern['content'] : '';
}

/*
 * Page definitions, in dependency order (parents before children).
 * 'content' may be supplied inline (the Legal index); otherwise it is pulled
 * from the named registered pattern.
 */
$gsley_legal_index = '<!-- wp:group {"tagName":"div","templateLock":"contentOnly","layout":{"type":"default"}} -->'
	. '<div class="wp-block-group">'
	. '<!-- wp:group {"tagName":"section","className":"page-hero","layout":{"type":"default"}} -->'
	. '<section class="wp-block-group page-hero"><!-- wp:group {"className":"wrap read","layout":{"type":"default"}} --><div class="wp-block-group wrap read">'
	. '<!-- wp:html --><div class="crumb"><a href="/">Home</a> › Legal</div><!-- /wp:html -->'
	. '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Legal</h1><!-- /wp:heading -->'
	. '<!-- wp:paragraph {"className":"lede"} --><p class="lede">Our privacy, terms, and cookie policies.</p><!-- /wp:paragraph -->'
	. '</div><!-- /wp:group --></section><!-- /wp:group -->'
	. '<!-- wp:group {"tagName":"section","className":"section","layout":{"type":"default"}} --><section class="wp-block-group section"><!-- wp:group {"className":"wrap read legal","layout":{"type":"default"}} --><div class="wp-block-group wrap read legal">'
	. '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li><a href="/legal/privacy/">Privacy Policy</a></li><!-- /wp:list-item --><!-- wp:list-item --><li><a href="/legal/terms/">Terms of Service</a></li><!-- /wp:list-item --><!-- wp:list-item --><li><a href="/legal/cookies/">Cookie Policy</a></li><!-- /wp:list-item --></ul><!-- /wp:list -->'
	. '</div><!-- /wp:group --></section><!-- /wp:group -->'
	. '</div><!-- /wp:group -->';

$gsley_pages = array(
	array( 'slug' => 'about',                  'title' => 'About',                'parent' => null,       'pattern' => 'gsley-digital/page-about' ),
	array( 'slug' => 'services',               'title' => 'Services',             'parent' => null,       'pattern' => 'gsley-digital/page-services' ),
	array( 'slug' => 'market',                 'title' => 'Market',               'parent' => 'services', 'pattern' => 'gsley-digital/page-services-market' ),
	array( 'slug' => 'digital',                'title' => 'Digital',              'parent' => 'services', 'pattern' => 'gsley-digital/page-services-digital' ),
	array( 'slug' => 'automation-and-revops',  'title' => 'Automation & RevOps',  'parent' => 'digital',  'pattern' => 'gsley-digital/page-service-automation-revops' ),
	array( 'slug' => 'ai-adoption-programmes', 'title' => 'AI Adoption Programmes','parent' => 'digital',  'pattern' => 'gsley-digital/page-service-ai-adoption' ),

	// Market service detail pages.
	array( 'slug' => 'website-design',                  'title' => 'Website Design & Development',            'parent' => 'market', 'pattern' => 'gsley-digital/page-service-website-design' ),
	array( 'slug' => 'google-ads',                      'title' => 'Google Ads',                              'parent' => 'market', 'pattern' => 'gsley-digital/page-service-google-ads' ),
	array( 'slug' => 'email-marketing',                 'title' => 'Email Marketing',                         'parent' => 'market', 'pattern' => 'gsley-digital/page-service-email-marketing' ),
	array( 'slug' => 'social-media-marketing',          'title' => 'Social Media Marketing',                  'parent' => 'market', 'pattern' => 'gsley-digital/page-service-social-media-marketing' ),
	array( 'slug' => 'social-media-management',         'title' => 'Social Media Management',                 'parent' => 'market', 'pattern' => 'gsley-digital/page-service-social-media-management' ),
	array( 'slug' => 'content-development',             'title' => 'Content Development',                     'parent' => 'market', 'pattern' => 'gsley-digital/page-service-content-development' ),
	array( 'slug' => 'demand-generation',               'title' => 'Demand Generation Systems',               'parent' => 'market', 'pattern' => 'gsley-digital/page-service-demand-generation' ),
	array( 'slug' => 'b2b-sales-enablement',            'title' => 'B2B Sales Enablement & Commercialisation','parent' => 'market', 'pattern' => 'gsley-digital/page-service-b2b-sales-enablement' ),
	array( 'slug' => 'strategic-partnerships',          'title' => 'Strategic Partnerships',                  'parent' => 'market', 'pattern' => 'gsley-digital/page-service-strategic-partnerships' ),
	array( 'slug' => 'institutional-brand-and-reputation','title' => 'Institutional Brand & Reputation',      'parent' => 'market', 'pattern' => 'gsley-digital/page-service-institutional-brand-and-reputation' ),
	array( 'slug' => 'thought-leadership-programmes',   'title' => 'Thought-Leadership Programmes',           'parent' => 'market', 'pattern' => 'gsley-digital/page-service-thought-leadership-programmes' ),
	array( 'slug' => 'marketing-automation',            'title' => 'Marketing Automation',                    'parent' => 'market', 'pattern' => 'gsley-digital/page-service-marketing-automation' ),

	// Digital service detail pages.
	array( 'slug' => 'ai-agent-deployment',             'title' => 'AI Agent Deployment',                     'parent' => 'digital','pattern' => 'gsley-digital/page-service-ai-agent-deployment' ),
	array( 'slug' => 'digital-transformation',          'title' => 'Digital Transformation',                  'parent' => 'digital','pattern' => 'gsley-digital/page-service-digital-transformation' ),
	array( 'slug' => 'data-infrastructure',             'title' => 'Data Infrastructure',                     'parent' => 'digital','pattern' => 'gsley-digital/page-service-data-infrastructure' ),
	array( 'slug' => 'revenue-optimisation',            'title' => 'Revenue Optimisation',                    'parent' => 'digital','pattern' => 'gsley-digital/page-service-revenue-optimisation' ),

	array( 'slug' => 'health',                          'title' => 'GSLEY Health',                            'parent' => 'services', 'pattern' => 'gsley-digital/page-services-health' ),
	array( 'slug' => 'pr-marketing-communications',     'title' => 'Healthcare PR, Marketing & Communications', 'parent' => 'health', 'pattern' => 'gsley-digital/page-service-health-pr-marketing-communications' ),
	array( 'slug' => 'revenue-and-tariff-optimisation', 'title' => 'Revenue & Tariff Optimisation',           'parent' => 'health', 'pattern' => 'gsley-digital/page-service-health-revenue-and-tariff-optimisation' ),
	array( 'slug' => 'thought-leadership',              'title' => 'Healthcare Thought Leadership',           'parent' => 'health', 'pattern' => 'gsley-digital/page-service-health-thought-leadership' ),

	array( 'slug' => 'tools',                  'title' => 'Tools',                'parent' => null,       'pattern' => 'gsley-digital/page-tools' ),
	array( 'slug' => 'contact',                'title' => 'Contact',              'parent' => null,       'pattern' => 'gsley-digital/page-contact' ),
	array( 'slug' => 'legal',                  'title' => 'Legal',                'parent' => null,       'content' => $gsley_legal_index ),
	array( 'slug' => 'privacy',                'title' => 'Privacy Policy',       'parent' => 'legal',    'pattern' => 'gsley-digital/page-legal-privacy' ),
	array( 'slug' => 'terms',                  'title' => 'Terms of Service',     'parent' => 'legal',    'pattern' => 'gsley-digital/page-legal-terms' ),
	array( 'slug' => 'cookies',                'title' => 'Cookie Policy',        'parent' => 'legal',    'pattern' => 'gsley-digital/page-legal-cookies' ),
);

$ids      = array();
$created  = 0;
$updated  = 0;

foreach ( $gsley_pages as $def ) {
	$parent_id = ( $def['parent'] && isset( $ids[ $def['parent'] ] ) ) ? $ids[ $def['parent'] ] : 0;
	$content   = isset( $def['content'] ) ? $def['content'] : gsley_pattern_content( $def['pattern'] );

	if ( '' === $content ) {
		WP_CLI::warning( "No content for '{$def['slug']}' — is the theme active and the pattern registered? Skipped." );
		continue;
	}

	// Find an existing page with this slug under this parent.
	$existing = get_posts(
		array(
			'post_type'        => 'page',
			'name'             => $def['slug'],
			'post_parent'      => $parent_id,
			'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
			'numberposts'      => 1,
			'suppress_filters' => false,
		)
	);

	$postarr = array(
		'post_type'    => 'page',
		'post_title'   => $def['title'],
		'post_name'    => $def['slug'],
		'post_parent'  => $parent_id,
		'post_status'  => 'publish',
		'post_content' => $content,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing[0]->ID;
		$page_id       = wp_update_post( $postarr, true );
		$updated++;
	} else {
		$page_id = wp_insert_post( $postarr, true );
		$created++;
	}

	if ( is_wp_error( $page_id ) ) {
		WP_CLI::warning( "Failed '{$def['slug']}': " . $page_id->get_error_message() );
		continue;
	}

	// Full-width template for every built page.
	update_post_meta( $page_id, '_wp_page_template', 'page-wide' );
	$ids[ $def['slug'] ] = $page_id;

	$path = get_page_uri( $page_id );
	WP_CLI::log( "✓ /{$path}  (#{$page_id})" );
}

WP_CLI::success( "Pages imported — {$created} created, {$updated} updated. Visit Pages in wp-admin to edit." );
