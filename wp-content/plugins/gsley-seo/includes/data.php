<?php
/**
 * GSLEY Diagnostics — shared data + page-type detection for the SEO layer.
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service leaves per pillar. Read from the GSLEY Diagnostics registry when the
 * plugin is active (single source of truth); the arrays below are the fallback.
 */
function gsley_seo_registry_services( $pillar ) {
	if ( ! function_exists( 'gsley_services' ) ) {
		return null;
	}
	$all = gsley_services();
	if ( empty( $all[ $pillar ] ) ) {
		return array();
	}
	$out = array();
	foreach ( $all[ $pillar ] as $slug => $svc ) {
		$out[ $slug ] = $svc['label'];
	}
	return $out;
}
function gsley_seo_market_services() {
	$r = gsley_seo_registry_services( 'market' );
	if ( null !== $r ) {
		return $r;
	}
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
	$r = gsley_seo_registry_services( 'digital' );
	if ( null !== $r ) {
		return $r;
	}
	return array(
		'ai-agent-deployment' => 'AI Agent Deployment',
		'automation-and-revops' => 'Automation & RevOps',
		'ai-adoption-programmes' => 'AI Adoption Programmes',
		'digital-transformation' => 'Digital Transformation',
		'data-infrastructure' => 'Data Infrastructure',
		'revenue-optimisation' => 'Revenue Optimisation',
	);
}
function gsley_seo_health_services() {
	$r = gsley_seo_registry_services( 'health' );
	if ( null !== $r ) {
		return $r;
	}
	return array(
		'pr-marketing-communications' => 'Healthcare PR, Marketing & Communications',
		'revenue-and-tariff-optimisation' => 'Revenue & Tariff Optimisation',
		'thought-leadership' => 'Healthcare Thought Leadership',
	);
}
/** Pillar slug => [services, label]. */
function gsley_seo_pillars() {
	return array(
		'market'  => array( gsley_seo_market_services(), 'Market', 'GSLEY Market' ),
		'digital' => array( gsley_seo_digital_services(), 'Digital', 'GSLEY Digital' ),
		'health'  => array( gsley_seo_health_services(), 'Health', 'GSLEY Health' ),
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
		'thought-leadership' => 'thought-leadership-health',
	);
}

/** The request path relative to the site root, no leading/trailing slash. */
function gsley_seo_current_path() {
	$req  = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/';
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
	if ( 2 === count( $seg ) && 'services' === $seg[0] && isset( gsley_seo_pillars()[ $seg[1] ] ) ) {
		return array( 'type' => 'pillar', 'pillar' => $seg[1] );
	}
	if ( 'tools' === $path ) {
		return array( 'type' => 'tools-hub' );
	}
	if ( 3 === count( $seg ) && 'services' === $seg[0] ) {
		$pillars = gsley_seo_pillars();
		if ( isset( $pillars[ $seg[1] ][0][ $seg[2] ] ) ) {
			return array( 'type' => 'service', 'pillar' => $seg[1], 'slug' => $seg[2], 'name' => $pillars[ $seg[1] ][0][ $seg[2] ] );
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
		case 'services-index': return 'services';
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
		case 'about': $crumbs[] = array( 'About', home_url( '/about/' ) ); break;
		case 'contact': $crumbs[] = array( 'Contact', home_url( '/contact/' ) ); break;
		case 'services-index': $crumbs[] = array( 'Services', home_url( '/services/' ) ); break;
		case 'tools-hub': $crumbs[] = array( 'Tools', home_url( '/tools/' ) ); break;
		case 'pillar':
			$crumbs[] = array( 'Services', home_url( '/services/' ) );
			$crumbs[] = array( gsley_seo_pillars()[ $pt['pillar'] ][1], trailingslashit( home_url( '/services/' . $pt['pillar'] ) ) );
			break;
		case 'service':
			$crumbs[] = array( 'Services', home_url( '/services/' ) );
			$crumbs[] = array( gsley_seo_pillars()[ $pt['pillar'] ][1], trailingslashit( home_url( '/services/' . $pt['pillar'] ) ) );
			$crumbs[] = array( $pt['name'], trailingslashit( home_url( '/services/' . $pt['pillar'] . '/' . $pt['slug'] ) ) );
			break;
		case 'tool':
			$crumbs[] = array( 'Tools', home_url( '/tools/' ) );
			$crumbs[] = array( $pt['name'], trailingslashit( home_url( '/tools/' . $pt['slug'] ) ) );
			break;
		case 'legal':
			$crumbs[] = array( 'Legal', home_url( '/legal/' ) );
			$crumbs[] = array( $pt['name'], trailingslashit( home_url( '/legal/' . $pt['slug'] ) ) );
			break;
	}
	return $crumbs;
}

/** A site fact from GSLEY Settings (diagnostics plugin), with a fallback. */
function gsley_seo_site( $key, $fallback = '' ) {
	if ( function_exists( 'gsley_site' ) ) {
		$v = gsley_site( $key );
		return '' !== $v ? $v : $fallback;
	}
	return $fallback;
}

/** A schema.org Place for an office; the address is included only once a street is set. */
function gsley_seo_place( $o ) {
	$addr = array( '@type' => 'PostalAddress', 'addressLocality' => $o['city'], 'addressCountry' => 'NG' );
	$region = array( 'Lagos' => 'Lagos', 'Enugu' => 'Enugu' );
	if ( isset( $region[ $o['city'] ] ) ) {
		$addr['addressRegion'] = $region[ $o['city'] ];
	}
	if ( ! empty( $o['street'] ) ) {
		$addr['streetAddress'] = $o['street'];
		if ( ! empty( $o['postcode'] ) ) {
			$addr['postalCode'] = $o['postcode'];
		}
	}
	$place = array( '@type' => 'Place', 'name' => 'GSLEY Digital — ' . $o['city'], 'address' => $addr );
	if ( ! empty( $o['phone'] ) ) {
		$place['telephone'] = $o['phone'];
	}
	if ( ! empty( $o['maps'] ) ) {
		$place['hasMap'] = $o['maps'];
	}
	return $place;
}

/** The site-wide Organization node (schema.org). No founder/Person node (owner decision, Oct 2026). */
function gsley_seo_organization_node() {
	$offices = function_exists( 'gsley_site_offices' ) ? gsley_site_offices() : array( array( 'city' => 'Lagos' ), array( 'city' => 'Enugu' ) );
	$places  = array();
	foreach ( $offices as $o ) {
		$places[] = gsley_seo_place( wp_parse_args( $o, array( 'city' => '', 'street' => '', 'postcode' => '', 'phone' => '', 'maps' => '' ) ) );
	}
	$same = array();
	if ( function_exists( 'gsley_site_socials' ) ) {
		foreach ( gsley_site_socials() as $l ) {
			$same[] = $l['href'];
		}
	} else {
		$same = array( 'https://www.linkedin.com/company/gsleydigital/', 'https://web.facebook.com/gsleydigital/', 'https://x.com/gsleydigital/', 'https://www.instagram.com/gsleydigital/' );
	}
	$phone = gsley_seo_site( 'phone', '+234 902 476 7079' );
	$node  = array(
		'@type'         => 'Organization',
		'@id'           => home_url( '/#organization' ),
		'name'          => 'GSLEY Digital',
		'alternateName' => 'GSLEY',
		'url'           => home_url( '/' ),
		'logo'          => gsley_seo_og_base() . '/home.png',
		'description'   => 'A productised growth and AI studio for institutions, ambitious scale-ups, and healthcare brands. Three pillars: predictable demand, AI-native operating systems, and healthcare growth.',
		'slogan'        => 'Engineered Precision',
		'knowsAbout'    => array( 'Demand generation', 'Brand and reputation management', 'Digital marketing', 'AI agent deployment', 'Marketing automation and RevOps', 'Digital transformation', 'Healthcare public relations and marketing', 'Healthcare revenue and tariff optimisation', 'Healthcare thought leadership' ),
		'areaServed'    => 'Worldwide',
		'contactPoint'  => array(
			array(
				'@type'             => 'ContactPoint',
				'telephone'         => $phone,
				'contactType'       => 'customer service',
				'email'             => gsley_seo_site( 'email', 'brief@gsleydigital.com' ),
				'availableLanguage' => array( 'English' ),
				'areaServed'        => 'Worldwide',
			),
		),
		'sameAs'        => $same,
	);
	if ( $places ) {
		// Lagos first: primary office for search.
		$node['address']  = $places[0]['address'];
		$node['location'] = $places;
	}
	return $node;
}

/** The six homepage FAQ (kept in sync with the theme's faq pattern). */
function gsley_seo_home_faq() {
	$email = gsley_seo_site( 'email', 'brief@gsleydigital.com' );
	$phone = gsley_seo_site( 'phone', '+234 902 476 7079' );
	return array(
		array( 'What is GSLEY Digital?', 'GSLEY Digital is a productised growth and AI studio with offices in Lagos and Enugu, Nigeria, serving institutions and ambitious B2B and B2C scale-ups worldwide. It operates three pillars — GSLEY Market for predictable demand and commercial systems, GSLEY Digital for AI-native operating systems, and GSLEY Health for healthcare PR, marketing, revenue, and thought leadership — delivered as one commercial system rather than disconnected campaigns.' ),
		array( 'What is The Archetype System™?', 'The Archetype System™ is GSLEY’s operating method. Instead of opinion-based consulting, every engagement runs on a documented archetype — a named, repeatable method with defined inputs, sequence, and outputs. The archetype is transferred to the client at the end of the engagement, so the system stays in the organisation rather than leaving with the consultant.' ),
		array( 'How is GSLEY different from a marketing agency?', 'A traditional agency sells deliverables and retains the method. GSLEY productises the method itself, ships AI-native delivery, and hands over a documented operating manual the internal team can run. The work is structured as named archetypes and an explicit value ladder — from free diagnostics to fractional executive retainers — rather than open-ended monthly retainers.' ),
		array( 'Who does GSLEY work with?', 'Three buyers: institutional leaders at hospitals, foundations, and parastatals; enterprise growth leaders (CMOs and CROs) at venture- or PE-backed B2B businesses; and founders of ambitious Nigerian and West African scale-ups building toward category leadership.' ),
		array( 'Where is GSLEY based?', 'GSLEY Digital has offices in Lagos and Enugu, Nigeria, and works with clients globally. Contact: ' . $email . ' or ' . $phone . '.' ),
		array( 'How do I start working with GSLEY?', 'Run a free diagnostic for an instant scored read-out and a recommendation, or book a discovery call directly. Engagements are scoped from there across three tiers: free, sprint, and retainer.' ),
	);
}
