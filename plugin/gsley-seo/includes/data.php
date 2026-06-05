<?php
/**
 * GSLEY Diagnostics — shared data + page-type detection for the SEO layer.
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Service leaves per pillar (match the routing map). */
function gsley_seo_market_services() {
	return array(
		'website-design' => 'Website Design & Development',
		'google-ads' => 'Google Ads',
		'email-marketing' => 'Email Marketing',
		'social-media-marketing' => 'Social Media Marketing',
		'social-media-management' => 'Social Media Management',
		'content-development' => 'Content Development',
		'demand-generation' => 'Demand Generation Systems',
		'b2b-sales-enablement' => 'B2B Sales Enablement & Commercialisation',
		'strategic-partnerships' => 'Strategic Partnerships',
		'institutional-brand-and-reputation' => 'Institutional Brand & Reputation',
		'thought-leadership-programmes' => 'Thought-Leadership Programmes',
		'marketing-automation' => 'Marketing Automation',
	);
}
function gsley_seo_digital_services() {
	return array(
		'ai-agent-deployment' => 'AI Agent Deployment',
		'automation-and-revops' => 'Automation & RevOps',
		'ai-adoption-programmes' => 'AI Adoption Programmes',
		'digital-transformation' => 'Digital Transformation',
		'data-infrastructure' => 'Data Infrastructure',
		'revenue-optimisation' => 'Revenue Optimisation',
	);
}
function gsley_seo_tools() {
	return array(
		'growth-diagnostic' => 'The Gsley Growth Diagnostic™',
		'ai-adoption-assessment-organisation' => 'AI Adoption Assessment — Organisation',
		'ai-adoption-assessment-individual' => 'AI Adoption Assessment — Individual',
		'brand-authority-audit' => 'Brand Authority Audit',
		'revenue-leakage-calculator' => 'Revenue Leakage Calculator',
		'marketing-maturity-scorecard' => 'Marketing Maturity Scorecard',
		'automation-opportunity-finder' => 'Automation Opportunity Finder',
		'partnership-readiness-quiz' => 'Partnership Readiness Quiz',
		'social-media-audit' => 'Social Media Audit',
		'digital-presence-orm-audit' => 'Digital Presence & ORM Audit',
	);
}
function gsley_seo_legal() {
	return array( 'privacy' => 'Privacy Policy', 'terms' => 'Terms of Service', 'cookies' => 'Cookie Policy' );
}

/** OG slug aliases where the generated image slug differs from the URL leaf. */
function gsley_seo_og_alias() {
	return array(
		'institutional-brand-and-reputation' => 'institutional-brand-reputation',
		'thought-leadership-programmes' => 'thought-leadership',
		'social-media-management' => 'social-media-marketing',
	);
}

/** The request path relative to the site root, no leading/trailing slash. */
function gsley_seo_current_path() {
	$req  = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '/';
	$base = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$req  = '/' . ltrim( (string) $req, '/' );
	$base = '/' . trim( (string) $base, '/' ) . '/';
	if ( '//' !== $base && 0 === strpos( $req, rtrim( $base, '/' ) ) ) {
		$req = substr( $req, strlen( rtrim( $base, '/' ) ) );
	}
	return trim( (string) $req, '/' );
}

/**
 * Classify the current request into a page type for schema/meta.
 *
 * @return array { type, slug, name, pillar }
 */
function gsley_seo_page_type() {
	if ( is_front_page() || is_home() ) {
		return array( 'type' => 'home' );
	}
	$path = gsley_seo_current_path();
	$seg  = $path ? explode( '/', $path ) : array();

	if ( empty( $seg ) ) {
		return array( 'type' => 'home' );
	}
	if ( 'about' === $path ) {
		return array( 'type' => 'about' );
	}
	if ( 'contact' === $path ) {
		return array( 'type' => 'contact' );
	}
	if ( 'services' === $path ) {
		return array( 'type' => 'services-index' );
	}
	if ( 'services/market' === $path ) {
		return array( 'type' => 'pillar', 'pillar' => 'market' );
	}
	if ( 'services/digital' === $path ) {
		return array( 'type' => 'pillar', 'pillar' => 'digital' );
	}
	if ( 'tools' === $path ) {
		return array( 'type' => 'tools-hub' );
	}
	if ( 3 === count( $seg ) && 'services' === $seg[0] ) {
		$market  = gsley_seo_market_services();
		$digital = gsley_seo_digital_services();
		if ( isset( $market[ $seg[2] ] ) ) {
			return array( 'type' => 'service', 'pillar' => 'market', 'slug' => $seg[2], 'name' => $market[ $seg[2] ] );
		}
		if ( isset( $digital[ $seg[2] ] ) ) {
			return array( 'type' => 'service', 'pillar' => 'digital', 'slug' => $seg[2], 'name' => $digital[ $seg[2] ] );
		}
	}
	if ( 2 === count( $seg ) && 'tools' === $seg[0] ) {
		$tools = gsley_seo_tools();
		if ( isset( $tools[ $seg[1] ] ) ) {
			return array( 'type' => 'tool', 'slug' => $seg[1], 'name' => $tools[ $seg[1] ] );
		}
	}
	if ( 2 === count( $seg ) && 'legal' === $seg[0] ) {
		$legal = gsley_seo_legal();
		if ( isset( $legal[ $seg[1] ] ) ) {
			return array( 'type' => 'legal', 'slug' => $seg[1], 'name' => $legal[ $seg[1] ] );
		}
	}
	return array( 'type' => 'other' );
}

/** Resolve the OG image slug for the current page type. */
function gsley_seo_og_slug( $pt ) {
	switch ( $pt['type'] ) {
		case 'home': return 'home';
		case 'about': return 'about';
		case 'contact': return 'contact';
		case 'pillar': return 'services-' . $pt['pillar'];
		case 'tools-hub': return 'tools';
		case 'tool': return isset( $pt['slug'] ) ? $pt['slug'] : 'tools';
		case 'service':
			$alias = gsley_seo_og_alias();
			$slug  = $pt['slug'];
			return isset( $alias[ $slug ] ) ? $alias[ $slug ] : $slug;
		default: return 'home';
	}
}

/** Breadcrumb trail (array of [name,url]) for the current page, excluding home label. */
function gsley_seo_breadcrumbs( $pt ) {
	$crumbs = array( array( 'Home', home_url( '/' ) ) );
	switch ( $pt['type'] ) {
		case 'about': $crumbs[] = array( 'About', home_url( '/about' ) ); break;
		case 'contact': $crumbs[] = array( 'Contact', home_url( '/contact' ) ); break;
		case 'services-index': $crumbs[] = array( 'Services', home_url( '/services' ) ); break;
		case 'tools-hub': $crumbs[] = array( 'Tools', home_url( '/tools' ) ); break;
		case 'pillar':
			$crumbs[] = array( 'Services', home_url( '/services' ) );
			$crumbs[] = array( 'market' === $pt['pillar'] ? 'Market' : 'Digital', home_url( '/services/' . $pt['pillar'] ) );
			break;
		case 'service':
			$crumbs[] = array( 'Services', home_url( '/services' ) );
			$crumbs[] = array( 'market' === $pt['pillar'] ? 'Market' : 'Digital', home_url( '/services/' . $pt['pillar'] ) );
			$crumbs[] = array( $pt['name'], home_url( '/services/' . $pt['pillar'] . '/' . $pt['slug'] ) );
			break;
		case 'tool':
			$crumbs[] = array( 'Tools', home_url( '/tools' ) );
			$crumbs[] = array( $pt['name'], home_url( '/tools/' . $pt['slug'] ) );
			break;
		case 'legal':
			$crumbs[] = array( 'Legal', home_url( '/legal' ) );
			$crumbs[] = array( $pt['name'], home_url( '/legal/' . $pt['slug'] ) );
			break;
	}
	return $crumbs;
}

/** The site-wide Organization node (schema.org). */
function gsley_seo_organization_node() {
	return array(
		'@type'       => 'Organization',
		'@id'         => home_url( '/#organization' ),
		'name'        => 'GSLEY Digital',
		'alternateName' => 'GSLEY',
		'url'         => home_url( '/' ),
		'logo'        => home_url( '/assets/img/gsley-digital-wordmark.svg' ),
		'description' => 'A productised growth and AI studio for institutions and ambitious scale-ups. Predictable demand and AI-native systems. Engineered, not opinionated.',
		'slogan'      => 'Engineered Precision',
		'founder'     => array(
			'@type'    => 'Person',
			'name'     => 'Chukwudi Kingsley Eze',
			'jobTitle' => 'Founder',
			'sameAs'   => 'https://www.linkedin.com/in/chukwudihex',
		),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Lagos',
			'addressRegion'   => 'Lagos',
			'addressCountry'  => 'NG',
		),
		'contactPoint' => array(
			array(
				'@type'           => 'ContactPoint',
				'telephone'       => '+234-902-476-7079',
				'contactType'     => 'customer service',
				'email'           => 'brief@gsleydigital.com',
				'availableLanguage' => array( 'English' ),
				'areaServed'      => 'Worldwide',
			),
		),
		'sameAs' => array(
			'https://www.linkedin.com/company/gsleydigital/',
			'https://web.facebook.com/gsleydigital/',
			'https://x.com/gsleydigital/',
			'https://www.instagram.com/gsleydigital/',
		),
	);
}

/** The six homepage FAQ (kept in sync with the hero/faq pattern). */
function gsley_seo_home_faq() {
	return array(
		array( 'What is GSLEY Digital?', 'GSLEY Digital is a productised growth and AI studio based in Lagos, serving institutions and ambitious B2B and B2C scale-ups worldwide. It operates two pillars — GSLEY Market for predictable demand and commercial systems, and GSLEY Digital for AI-native operating systems — delivered as one commercial system rather than disconnected campaigns.' ),
		array( 'What is The Archetype System™?', 'The Archetype System™ is GSLEY’s operating method. Instead of opinion-based consulting, every engagement runs on a documented archetype — a named, repeatable method with defined inputs, sequence, and outputs. The archetype is transferred to the client at the end of the engagement, so the system stays in the organisation rather than leaving with the consultant.' ),
		array( 'How is GSLEY different from a marketing agency?', 'A traditional agency sells deliverables and retains the method. GSLEY productises the method itself, ships AI-native delivery, and hands over a documented operating manual the internal team can run. The work is structured as named archetypes and an explicit value ladder — from free diagnostics to fractional executive retainers — rather than open-ended monthly retainers.' ),
		array( 'Who does GSLEY work with?', 'Three buyers: institutional leaders at hospitals, foundations, and parastatals; enterprise growth leaders (CMOs and CROs) at venture- or PE-backed B2B businesses; and founders of ambitious Nigerian and West African scale-ups building toward category leadership.' ),
		array( 'Where is GSLEY based?', 'GSLEY Digital is headquartered in Lagos, Nigeria, and works with clients globally. Contact: brief@gsleydigital.com or +234 902 476 7079.' ),
		array( 'How do I start working with GSLEY?', 'Run a free diagnostic for an instant scored read-out and a recommendation, or book a discovery call directly. Engagements are scoped from there across four tiers — free, toolkit, sprint, and retainer.' ),
	);
}
