<?php
/**
 * GSLEY Diagnostics — one-click page importer (admin).
 *
 * Same logic as wp-content/themes/gsley-digital/bin/import-pages.php, exposed as a button
 * under GSLEY Leads → Setup, so managed hosts without WP-CLI (e.g. EasyWP) can
 * build all the routed pages from the theme's block patterns in one click.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'gsley_setup_menu', 11 );
function gsley_setup_menu() {
	add_submenu_page( 'gsley-leads', 'Setup', 'Setup', 'manage_options', 'gsley-setup', 'gsley_render_setup' );
}

/** Pattern markup by slug (patterns are registered by the active theme). */
function gsley_setup_pattern_content( $slug ) {
	if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return '';
	}
	$p = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	return $p ? $p['content'] : '';
}

/** The page definitions, in dependency order (parents before children). */
function gsley_setup_page_defs() {
	$legal_index = '<!-- wp:group {"tagName":"div","templateLock":"contentOnly","layout":{"type":"default"}} -->'
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

	return array(
		array( 'about', 'About', null, 'gsley-digital/page-about', null ),
		array( 'services', 'Services', null, 'gsley-digital/page-services', null ),
		array( 'market', 'Market', 'services', 'gsley-digital/page-services-market', null ),
		array( 'digital', 'Digital', 'services', 'gsley-digital/page-services-digital', null ),
		array( 'automation-and-revops', 'Automation & RevOps', 'digital', 'gsley-digital/page-service-automation-revops', null ),
		array( 'ai-adoption-programmes', 'AI Adoption Programmes', 'digital', 'gsley-digital/page-service-ai-adoption', null ),
		array( 'website-design', 'Website Design & Development', 'market', 'gsley-digital/page-service-website-design', null ),
		array( 'google-ads', 'Google Ads', 'market', 'gsley-digital/page-service-google-ads', null ),
		array( 'email-marketing', 'Email Marketing', 'market', 'gsley-digital/page-service-email-marketing', null ),
		array( 'social-media-marketing', 'Social Media Marketing', 'market', 'gsley-digital/page-service-social-media-marketing', null ),
		array( 'social-media-management', 'Social Media Management', 'market', 'gsley-digital/page-service-social-media-management', null ),
		array( 'content-development', 'Content Development', 'market', 'gsley-digital/page-service-content-development', null ),
		array( 'demand-generation', 'Demand Generation Systems', 'market', 'gsley-digital/page-service-demand-generation', null ),
		array( 'b2b-sales-enablement', 'B2B Sales Enablement & Commercialisation', 'market', 'gsley-digital/page-service-b2b-sales-enablement', null ),
		array( 'strategic-partnerships', 'Strategic Partnerships', 'market', 'gsley-digital/page-service-strategic-partnerships', null ),
		array( 'institutional-brand-and-reputation', 'Institutional Brand & Reputation', 'market', 'gsley-digital/page-service-institutional-brand-and-reputation', null ),
		array( 'thought-leadership-programmes', 'Thought-Leadership Programmes', 'market', 'gsley-digital/page-service-thought-leadership-programmes', null ),
		array( 'marketing-automation', 'Marketing Automation', 'market', 'gsley-digital/page-service-marketing-automation', null ),
		array( 'ai-agent-deployment', 'AI Agent Deployment', 'digital', 'gsley-digital/page-service-ai-agent-deployment', null ),
		array( 'digital-transformation', 'Digital Transformation', 'digital', 'gsley-digital/page-service-digital-transformation', null ),
		array( 'data-infrastructure', 'Data Infrastructure', 'digital', 'gsley-digital/page-service-data-infrastructure', null ),
		array( 'revenue-optimisation', 'Revenue Optimisation', 'digital', 'gsley-digital/page-service-revenue-optimisation', null ),
		array( 'health', 'GSLEY Health', 'services', 'gsley-digital/page-services-health', null ),
		array( 'pr-marketing-communications', 'Healthcare PR, Marketing & Communications', 'health', 'gsley-digital/page-service-health-pr-marketing-communications', null ),
		array( 'revenue-and-tariff-optimisation', 'Revenue & Tariff Optimisation', 'health', 'gsley-digital/page-service-health-revenue-and-tariff-optimisation', null ),
		array( 'thought-leadership', 'Healthcare Thought Leadership', 'health', 'gsley-digital/page-service-health-thought-leadership', null ),
		array( 'tools', 'Tools', null, 'gsley-digital/page-tools', null ),
		// Individual tool pages — each embeds its diagnostic block (dynamic, rendered by the plugin).
		array( 'growth-diagnostic', 'The Gsley Growth Diagnostic™', 'tools', null, '<!-- wp:gsley/tool-growth-diagnostic /-->' ),
		array( 'ai-adoption-assessment-organisation', 'AI Adoption Assessment — Organisation', 'tools', null, '<!-- wp:gsley/tool-ai-adoption-assessment-organisation /-->' ),
		array( 'ai-adoption-assessment-individual', 'AI Adoption Assessment — Individual', 'tools', null, '<!-- wp:gsley/tool-ai-adoption-assessment-individual /-->' ),
		array( 'brand-authority-audit', 'Brand Authority Audit', 'tools', null, '<!-- wp:gsley/tool-brand-authority-audit /-->' ),
		array( 'revenue-leakage-calculator', 'Revenue Leakage Calculator', 'tools', null, '<!-- wp:gsley/tool-revenue-leakage-calculator /-->' ),
		array( 'marketing-maturity-scorecard', 'Marketing Maturity Scorecard', 'tools', null, '<!-- wp:gsley/tool-marketing-maturity-scorecard /-->' ),
		array( 'automation-opportunity-finder', 'Automation Opportunity Finder', 'tools', null, '<!-- wp:gsley/tool-automation-opportunity-finder /-->' ),
		array( 'partnership-readiness-quiz', 'Partnership Readiness Quiz', 'tools', null, '<!-- wp:gsley/tool-partnership-readiness-quiz /-->' ),
		array( 'social-media-audit', 'Social Media Audit', 'tools', null, '<!-- wp:gsley/tool-social-media-audit /-->' ),
		array( 'digital-presence-orm-audit', 'Digital Presence & ORM Audit', 'tools', null, '<!-- wp:gsley/tool-digital-presence-orm-audit /-->' ),
		array( 'contact', 'Contact', null, 'gsley-digital/page-contact', null ),
		array( 'legal', 'Legal', null, null, $legal_index ),
		array( 'privacy', 'Privacy Policy', 'legal', 'gsley-digital/page-legal-privacy', null ),
		array( 'terms', 'Terms of Service', 'legal', 'gsley-digital/page-legal-terms', null ),
		array( 'cookies', 'Cookie Policy', 'legal', 'gsley-digital/page-legal-cookies', null ),
	);
}

/**
 * Create or update every routed page. Idempotent.
 *
 * @return array { created:int, updated:int, log:array<string>, missing:array<string> }
 */
function gsley_setup_run_import() {
	$ids     = array();
	$created = 0;
	$updated = 0;
	$log     = array();
	$missing = array();

	foreach ( gsley_setup_page_defs() as $def ) {
		list( $slug, $title, $parent_slug, $pattern, $inline ) = $def;
		$parent_id = ( $parent_slug && isset( $ids[ $parent_slug ] ) ) ? $ids[ $parent_slug ] : 0;
		$content   = null !== $inline ? $inline : gsley_setup_pattern_content( $pattern );

		if ( '' === $content || null === $content ) {
			$missing[] = $slug . ' (' . ( $pattern ? $pattern : 'inline' ) . ')';
			continue;
		}

		$existing = get_posts(
			array(
				'post_type'        => 'page',
				'name'             => $slug,
				'post_parent'      => $parent_id,
				'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
				'numberposts'      => 1,
				'suppress_filters' => false,
			)
		);

		$postarr = array(
			'post_type'    => 'page',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_parent'  => $parent_id,
			'post_status'  => 'publish',
			'post_content' => $content,
		);

		if ( $existing ) {
			$postarr['ID'] = $existing[0]->ID;
			$page_id       = wp_update_post( $postarr, true );
			$verb          = 'updated';
			$updated++;
		} else {
			$page_id = wp_insert_post( $postarr, true );
			$verb    = 'created';
			$created++;
		}

		if ( is_wp_error( $page_id ) ) {
			$log[] = '✗ ' . $slug . ' — ' . $page_id->get_error_message();
			if ( 'created' === $verb ) {
				$created--;
			} else {
				$updated--;
			}
			continue;
		}

		update_post_meta( $page_id, '_wp_page_template', 'page-wide' );
		$ids[ $slug ] = $page_id;
		$log[]        = '✓ /' . get_page_uri( $page_id ) . '  (' . $verb . ')';
	}

	return array( 'created' => $created, 'updated' => $updated, 'log' => $log, 'missing' => $missing );
}

function gsley_render_setup() {
	$result = null;
	if ( isset( $_POST['gsley_import_pages'] ) && check_admin_referer( 'gsley_import_pages' ) && current_user_can( 'manage_options' ) ) {
		$result = gsley_setup_run_import();
	}

	$theme_ok = (bool) gsley_setup_pattern_content( 'gsley-digital/page-about' );

	echo '<div class="wrap"><h1>GSLEY Setup</h1>';
	echo '<p>Build every page in the site map (About, Services and the three pillar hubs, all service pages, Tools, Contact, and the legal pages) from the theme’s block patterns — with the correct nesting and the full-width template. Safe to run more than once: it updates existing pages instead of duplicating them.</p>';

	if ( ! $theme_ok ) {
		echo '<div class="notice notice-error"><p><strong>The GSLEY Digital theme isn’t active (or its patterns aren’t loaded).</strong> Activate the theme under Appearance → Themes first, then come back here.</p></div>';
	}

	if ( is_array( $result ) ) {
		echo '<div class="notice notice-success"><p><strong>Done.</strong> ' . (int) $result['created'] . ' created, ' . (int) $result['updated'] . ' updated.</p></div>';
		if ( ! empty( $result['missing'] ) ) {
			echo '<div class="notice notice-warning"><p><strong>Skipped (pattern not found):</strong> ' . esc_html( implode( ', ', $result['missing'] ) ) . '. Make sure the GSLEY Digital theme is active and up to date.</p></div>';
		}
		echo '<details open style="margin:12px 0"><summary style="cursor:pointer;font-weight:600">Details</summary><pre style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:12px;max-height:420px;overflow:auto">';
		echo esc_html( implode( "\n", $result['log'] ) );
		echo '</pre></details>';
		echo '<p><a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">View all pages</a> ';
		echo '<a class="button button-primary" href="' . esc_url( home_url( '/' ) ) . '" target="_blank">Open the site</a></p>';
	}

	echo '<form method="post" style="margin-top:16px">';
	wp_nonce_field( 'gsley_import_pages' );
	echo '<p><button type="submit" name="gsley_import_pages" value="1" class="button button-primary button-hero"' . ( $theme_ok ? '' : ' disabled' ) . '>Import / update pages</button></p>';
	echo '</form>';

	echo '<hr><h2>After importing</h2><ol>';
	echo '<li>Go to <strong>Settings → Permalinks</strong> and click <strong>Save Changes</strong> once (refreshes the URL rules).</li>';
	echo '<li>All ten tool pages are created automatically under <em>/tools/</em> — visit <a href="' . esc_url( home_url( '/tools/growth-diagnostic/' ) ) . '" target="_blank">/tools/growth-diagnostic</a> to confirm.</li>';
	echo '<li>Set up Resend under <a href="' . esc_url( admin_url( 'admin.php?page=gsley-diagnostics-settings' ) ) . '">Settings</a> so emails send.</li>';
	echo '</ol></div>';
}
