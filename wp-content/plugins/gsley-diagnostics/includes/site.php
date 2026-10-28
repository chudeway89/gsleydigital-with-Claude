<?php
/**
 * GSLEY Diagnostics — site facts + registry (single source of truth).
 *
 * - GSLEY Settings: email, phone, WhatsApp, location, hours, booking URL,
 *   socials and offices. Edited under GSLEY Leads → GSLEY Settings.
 * - Registry: services (three pillars) and tools. Footer columns, the contact
 *   dropdown, counts and the sitemap are generated from it.
 * - Output: [gsley key="…"], [gsley_count …] and friends, the `gsley/site`
 *   block-bindings source, and a render filter that resolves data-bind /
 *   data-count attributes in theme markup, so copy never hard-codes a fact.
 *
 * Seed values: redesign/site.js (SITE.contact, SITE.socials, SITE.services,
 * SITE.tools).
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================ SETTINGS */

/** Default values (redesign/site.js). */
function gsley_site_defaults() {
	return array(
		'email'          => 'brief@gsleydigital.com',
		'phone'          => '+234 902 476 7079',
		'phone_tel'      => '+2349024767079',
		'whatsapp'       => '2349024767079',
		'whatsapp_text'  => "Hi GSLEY, I'd like to talk about a project.",
		'location'       => 'Lagos · Enugu, Nigeria · working worldwide',
		'hours'          => '',
		'booking_url'    => '',
		'tagline'        => 'A productised growth and AI studio. Three pillars, one commercial system — engineered for ambitious institutions and healthcare brands worldwide.',
		'linkedin'       => 'https://www.linkedin.com/company/gsleydigital/',
		'x'              => 'https://x.com/gsleydigital/',
		'instagram'      => 'https://www.instagram.com/gsleydigital/',
		'facebook'       => 'https://web.facebook.com/gsleydigital/',
		'youtube'        => '',
		// Offices: Lagos first (primary for search). Street addresses stay empty
		// until the owner adds verified ones.
		'offices'        => array(
			array( 'city' => 'Lagos', 'street' => '', 'postcode' => '', 'phone' => '', 'maps' => '' ),
			array( 'city' => 'Enugu', 'street' => '', 'postcode' => '', 'phone' => '', 'maps' => '' ),
		),
	);
}

/** All settings, saved values over defaults. */
function gsley_site_settings() {
	static $cache = null;
	if ( null !== $cache && ! is_admin() ) {
		return $cache;
	}
	$saved = get_option( 'gsley_site', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = gsley_site_defaults();
	foreach ( $out as $k => $v ) {
		if ( array_key_exists( $k, $saved ) ) {
			$out[ $k ] = $saved[ $k ];
		}
	}
	$cache = $out;
	return $out;
}

/** One value, including derived keys (whatsapp_url, phone_href, booking_href). */
function gsley_site( $key ) {
	$s = gsley_site_settings();
	switch ( $key ) {
		case 'whatsapp_url':
			return 'https://wa.me/' . preg_replace( '/\D+/', '', $s['whatsapp'] ) . ( '' !== $s['whatsapp_text'] ? '?text=' . rawurlencode( $s['whatsapp_text'] ) : '' );
		case 'phone_href':
			return 'tel:' . preg_replace( '/[^\d+]/', '', $s['phone_tel'] ? $s['phone_tel'] : $s['phone'] );
		case 'email_href':
			return 'mailto:' . $s['email'];
		case 'booking_href':
			return $s['booking_url'] ? $s['booking_url'] : home_url( '/contact/#brief' );
		case 'year':
			return gmdate( 'Y' );
	}
	return isset( $s[ $key ] ) && is_string( $s[ $key ] ) ? $s[ $key ] : '';
}

/** Social links, in display order, only those filled in. */
function gsley_site_socials() {
	$labels = array( 'linkedin' => 'LinkedIn', 'x' => 'X', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube' );
	$out    = array();
	foreach ( $labels as $k => $label ) {
		$url = gsley_site( $k );
		if ( $url ) {
			$out[] = array( 'label' => $label, 'href' => $url );
		}
	}
	return $out;
}

/** Offices with a city name. */
function gsley_site_offices() {
	$out = array();
	foreach ( (array) gsley_site_settings()['offices'] as $o ) {
		if ( ! empty( $o['city'] ) ) {
			$out[] = wp_parse_args( $o, array( 'city' => '', 'street' => '', 'postcode' => '', 'phone' => '', 'maps' => '' ) );
		}
	}
	return $out;
}

/* ============================================================ REGISTRY */

/** The three pillars. */
function gsley_pillars() {
	return array(
		'market'  => array( 'name' => 'GSLEY Market', 'short' => 'Market', 'glyph' => '▲', 'sub' => 'Growth & Marketing' ),
		'digital' => array( 'name' => 'GSLEY Digital', 'short' => 'Digital', 'glyph' => '◇', 'sub' => 'AI-Native Systems' ),
		'health'  => array( 'name' => 'GSLEY Health', 'short' => 'Health', 'glyph' => '✚', 'sub' => 'Healthcare Growth' ),
	);
}

/**
 * Services by pillar: slug => [label, short, front].
 * Social Media Management keeps its own page (owner decision), so Market has twelve.
 */
function gsley_services() {
	$s = array(
		'market'  => array(
			'website-design'                     => array( 'Website Design & Development', 'Website Design', true ),
			'google-ads'                         => array( 'Google Ads', 'Google Ads', true ),
			'email-marketing'                    => array( 'Email Marketing', 'Email Marketing', true ),
			'social-media-marketing'             => array( 'Social Media Marketing', 'Social Media', true ),
			'social-media-management'            => array( 'Social Media Management', 'Social Media Management', false ),
			'content-development'                => array( 'Content Development', 'Content', true ),
			'demand-generation'                  => array( 'Demand Generation Systems', 'Demand Generation', false ),
			'b2b-sales-enablement'               => array( 'B2B Sales Enablement & Commercialisation', 'B2B Sales Enablement', false ),
			'strategic-partnerships'             => array( 'Strategic Partnerships', 'Strategic Partnerships', false ),
			'institutional-brand-and-reputation' => array( 'Institutional Brand & Reputation', 'Brand & Reputation', false ),
			'thought-leadership-programmes'      => array( 'Thought-Leadership Programmes', 'Thought Leadership', false ),
			'marketing-automation'               => array( 'Marketing Automation', 'Marketing Automation', false ),
		),
		'digital' => array(
			'ai-agent-deployment'    => array( 'AI Agent Deployment', 'AI Agent Deployment', true ),
			'automation-and-revops'  => array( 'Automation & RevOps', 'Automation & RevOps', true ),
			'ai-adoption-programmes' => array( 'AI Adoption Programmes', 'AI Adoption', true ),
			'digital-transformation' => array( 'Digital Transformation', 'Digital Transformation', false ),
			'data-infrastructure'    => array( 'Data Infrastructure', 'Data Infrastructure', false ),
			'revenue-optimisation'   => array( 'Revenue Optimisation', 'Revenue Optimisation', false ),
		),
		'health'  => array(
			'pr-marketing-communications'     => array( 'Healthcare PR, Marketing & Communications', 'PR, Marketing & Comms', true ),
			'revenue-and-tariff-optimisation' => array( 'Revenue & Tariff Optimisation', 'Revenue & Tariffs', true ),
			'thought-leadership'              => array( 'Healthcare Thought Leadership', 'Thought Leadership', true ),
		),
	);
	// One-line card copy for the front-line services (Services index, start-here grids).
	$desc = array(
		'website-design'                  => 'Conversion-engineered and AI-search ready.',
		'google-ads'                      => 'Productised search and performance media, measured against pipeline.',
		'email-marketing'                 => 'Lifecycle systems, not blasts. Built on owned data.',
		'social-media-marketing'          => 'Named-account targeting, paid amplification, measurable pipeline.',
		'content-development'             => 'Thought-leadership content engineered for citation, not vanity metrics.',
		'ai-agent-deployment'             => 'Agentic workflows wired into the back office, under explicit governance.',
		'automation-and-revops'           => 'Revenue operations and automation across the funnel — built on the data model.',
		'ai-adoption-programmes'          => 'Governed rollout of AI across teams — bias review, training, board-ready plan.',
		'pr-marketing-communications'     => 'Clinically literate communications, a standing press office, and a rehearsed crisis plan.',
		'revenue-and-tariff-optimisation' => 'Tariffs reset against cost and market, payer contracts reviewed, revenue leaks traced.',
		'thought-leadership'              => 'Clinicians and executives positioned and published, every piece checked for clinical accuracy.',
	);
	$out = array();
	foreach ( $s as $pillar => $list ) {
		foreach ( $list as $slug => $row ) {
			$out[ $pillar ][ $slug ] = array(
				'desc'   => isset( $desc[ $slug ] ) ? $desc[ $slug ] : '',
				'slug'   => $slug,
				'label'  => $row[0],
				'short'  => $row[1],
				'front'  => $row[2],
				'pillar' => $pillar,
				'url'    => home_url( '/services/' . $pillar . '/' . $slug . '/' ),
			);
		}
	}
	return apply_filters( 'gsley_services', $out );
}

/** Flat slug => service lookup. */
function gsley_service( $slug ) {
	foreach ( gsley_services() as $list ) {
		if ( isset( $list[ $slug ] ) ) {
			return $list[ $slug ];
		}
	}
	return null;
}

/** Tools: slug => [name, time, out, paid, flagship, price]. Built from the tool configs. */
function gsley_tools() {
	$meta = array(
		'growth-diagnostic'                   => array( '4 min', 'Scored read-out', false, true ),
		'ai-adoption-assessment-organisation' => array( '6 min', 'Board-ready read-out', false, false ),
		'ai-adoption-assessment-individual'   => array( '4 min', 'Scored read-out', false, false ),
		'brand-authority-audit'               => array( '5 min', 'Scored read-out', false, false ),
		'revenue-leakage-calculator'          => array( '3 min', 'Figure + breakdown', false, false ),
		'marketing-maturity-scorecard'        => array( '5 min', 'Scored read-out', false, false ),
		'automation-opportunity-finder'       => array( '4 min', 'Mapped opportunities', false, false ),
		'partnership-readiness-quiz'          => array( '3 min', 'Scored read-out', false, false ),
		'social-media-audit'                  => array( 'Live check', 'Free score · fix list $10', true, false ),
		'digital-presence-orm-audit'          => array( 'Live check', 'Free score · full plan $20', true, false ),
	);
	$out = array();
	foreach ( gsley_diagnostics_configs() as $slug => $cfg ) {
		$m            = isset( $meta[ $slug ] ) ? $meta[ $slug ] : array( '', 'Scored read-out', ! empty( $cfg['price'] ), false );
		$out[ $slug ] = array(
			'slug'     => $slug,
			'name'     => $cfg['title'],
			'time'     => $m[0],
			'out'      => $m[1],
			'paid'     => $m[2],
			'flagship' => $m[3],
			'price'    => isset( $cfg['priceLabel'] ) ? $cfg['priceLabel'] : '',
			'url'      => home_url( '/tools/' . $slug . '/' ),
		);
	}
	return apply_filters( 'gsley_tools', $out );
}

/** A count from the registry. */
function gsley_count( $type ) {
	$tools = gsley_tools();
	$svc   = gsley_services();
	$free  = count( array_filter( $tools, function ( $t ) { return ! $t['paid']; } ) );
	switch ( str_replace( '_', '-', $type ) ) {
		case 'tools-all':
		case 'all-tools':
			return count( $tools );
		case 'tools-all-others':
		case 'all-tools-others':
			return max( 0, count( $tools ) - 1 );
		case 'tools-free':
		case 'free-tools':
			return $free;
		case 'tools-free-others':
		case 'free-tools-others':
			return max( 0, $free - 1 );
		case 'tools-paid':
		case 'paid-tools':
			return count( $tools ) - $free;
		case 'market-services':
			return isset( $svc['market'] ) ? count( $svc['market'] ) : 0;
		case 'digital-services':
			return isset( $svc['digital'] ) ? count( $svc['digital'] ) : 0;
		case 'health-services':
			return isset( $svc['health'] ) ? count( $svc['health'] ) : 0;
		case 'all-services':
		case 'services':
			return array_sum( array_map( 'count', $svc ) );
		case 'front-services':
			$n = 0;
			foreach ( $svc as $list ) {
				foreach ( $list as $s ) {
					$n += $s['front'] ? 1 : 0;
				}
			}
			return $n;
		case 'pillars':
			return count( gsley_pillars() );
	}
	return 0;
}

/** 10 → "ten" (or "Ten"); numbers above twenty stay as digits. */
function gsley_number_word( $n, $cap = false ) {
	$w = array( 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty' );
	$s = isset( $w[ $n ] ) ? $w[ $n ] : (string) $n;
	return $cap ? ucfirst( $s ) : $s;
}

/* ============================================================ SHORTCODES */

add_action( 'init', 'gsley_site_register_shortcodes' );
function gsley_site_register_shortcodes() {
	add_shortcode( 'gsley', 'gsley_sc_value' );
	add_shortcode( 'gsley_count', 'gsley_sc_count' );
	add_shortcode( 'gsley_link', 'gsley_sc_link' );
	add_shortcode( 'gsley_socials', 'gsley_sc_socials' );
	add_shortcode( 'gsley_footer_services', 'gsley_sc_footer_services' );
	add_shortcode( 'gsley_service_options', 'gsley_sc_service_options' );
	add_shortcode( 'gsley_offices', 'gsley_sc_offices' );
	add_shortcode( 'gsley_booking', 'gsley_sc_booking' );
	add_shortcode( 'gsley_actions', 'gsley_sc_actions' );
	add_shortcode( 'gsley_service_cards', 'gsley_sc_service_cards' );
}

/** [gsley key="email"] — a plain value. */
function gsley_sc_value( $atts ) {
	$a = shortcode_atts( array( 'key' => '' ), $atts );
	$v = gsley_site( sanitize_key( $a['key'] ) );
	return in_array( $a['key'], array( 'whatsapp_url', 'booking_href', 'phone_href', 'email_href', 'booking_url' ), true ) ? esc_url( $v ) : esc_html( $v );
}

/** [gsley_count type="all-tools" format="word|number" cap="true"] */
function gsley_sc_count( $atts ) {
	$a = shortcode_atts( array( 'type' => 'all-tools', 'format' => 'word', 'cap' => 'false' ), $atts );
	$n = gsley_count( $a['type'] );
	return esc_html( 'number' === $a['format'] ? (string) $n : gsley_number_word( $n, 'true' === $a['cap'] ) );
}

/** [gsley_link type="email|phone|whatsapp|book" class="…"]Label[/gsley_link] */
function gsley_sc_link( $atts, $content = '' ) {
	$a    = shortcode_atts( array( 'type' => 'email', 'class' => '' ), $atts );
	$map  = array( 'email' => 'email_href', 'phone' => 'phone_href', 'whatsapp' => 'whatsapp_url', 'book' => 'booking_href' );
	$type = isset( $map[ $a['type'] ] ) ? $a['type'] : 'email';
	$text = '' !== trim( (string) $content ) ? $content : ( 'email' === $type ? gsley_site( 'email' ) : ( 'phone' === $type ? gsley_site( 'phone' ) : ( 'book' === $type ? 'Book a Discovery Call' : 'Chat on WhatsApp' ) ) );
	$ext  = 'whatsapp' === $type || ( 'book' === $type && gsley_site( 'booking_url' ) );
	return '<a href="' . esc_url( gsley_site( $map[ $type ] ) ) . '"' . ( $a['class'] ? ' class="' . esc_attr( $a['class'] ) . '"' : '' ) . ( $ext ? ' target="_blank" rel="noopener"' : '' ) . '>' . wp_kses_post( $text ) . '</a>';
}

/** [gsley_socials] — pill links. */
function gsley_sc_socials() {
	$links = gsley_site_socials();
	if ( ! $links ) {
		return '';
	}
	$html = '<div class="socials">';
	foreach ( $links as $l ) {
		$html .= '<a href="' . esc_url( $l['href'] ) . '" target="_blank" rel="noopener">' . esc_html( $l['label'] ) . '</a>';
	}
	return $html . '</div>';
}

/** [gsley_footer_services pillar="market"] — a footer column of front-line services. */
function gsley_sc_footer_services( $atts ) {
	$a       = shortcode_atts( array( 'pillar' => 'market' ), $atts );
	$pillars = gsley_pillars();
	$svc     = gsley_services();
	$p       = sanitize_key( $a['pillar'] );
	if ( ! isset( $pillars[ $p ], $svc[ $p ] ) ) {
		return '';
	}
	$dot  = array( 'market' => 'dot--m', 'digital' => 'dot--d', 'health' => 'dot--h' );
	$html = '<div class="footer-col"><h2><span class="dot ' . esc_attr( $dot[ $p ] ) . '"></span>' . esc_html( $pillars[ $p ]['name'] ) . '</h2><ul>';
	foreach ( $svc[ $p ] as $s ) {
		if ( $s['front'] ) {
			$html .= '<li><a href="' . esc_url( $s['url'] ) . '">' . esc_html( $s['short'] ) . '</a></li>';
		}
	}
	$html .= '<li><a href="' . esc_url( home_url( '/services/' . $p . '/' ) ) . '">All ' . esc_html( $pillars[ $p ]['short'] ) . ' services ↗</a></li></ul></div>';
	return $html;
}

/** [gsley_service_cards front="true" pillar=""] — service cards from the registry. */
function gsley_sc_service_cards( $atts ) {
	$a       = shortcode_atts( array( 'front' => 'true', 'pillar' => '' ), $atts );
	$pillars = gsley_pillars();
	$g       = array( 'market' => '', 'digital' => ' g--digital', 'health' => ' g--health' );
	$link    = array( 'market' => '', 'digital' => ' open-link--digital', 'health' => ' open-link--health' );
	$html    = '<div class="svc-grid">';
	foreach ( gsley_services() as $p => $list ) {
		if ( $a['pillar'] && $a['pillar'] !== $p ) {
			continue;
		}
		foreach ( $list as $s ) {
			if ( 'true' === $a['front'] && ! $s['front'] ) {
				continue;
			}
			$html .= '<a class="svc-card" href="' . esc_url( $s['url'] ) . '"><div class="tagrow"><span>' . esc_html( $pillars[ $p ]['short'] ) . '</span><span aria-hidden="true" class="' . esc_attr( trim( $g[ $p ] ) ) . '">' . esc_html( $pillars[ $p ]['glyph'] ) . '</span></div>'
				. '<h3>' . esc_html( $s['label'] ) . '</h3><p>' . esc_html( $s['desc'] ) . '</p><span class="open-link' . esc_attr( $link[ $p ] ) . '">' . ( $s['front'] ? 'Begin sprint ↗' : 'Open ↗' ) . '</span></a>';
		}
	}
	return $html . '</div>';
}

/** [gsley_service_options] — <optgroup>s for the brief form, grouped by pillar. */
function gsley_sc_service_options() {
	$html = '';
	foreach ( gsley_pillars() as $p => $info ) {
		$list = gsley_services();
		if ( empty( $list[ $p ] ) ) {
			continue;
		}
		$html .= '<optgroup label="' . esc_attr( $info['glyph'] . ' ' . $info['name'] ) . '">';
		foreach ( $list[ $p ] as $s ) {
			$html .= '<option value="' . esc_attr( $s['slug'] ) . '">' . esc_html( $s['label'] ) . '</option>';
		}
		$html .= '</optgroup>';
	}
	return $html;
}

/** [gsley_offices] — city names, plus addresses and directions once filled in. */
function gsley_sc_offices() {
	$offices = gsley_site_offices();
	if ( ! $offices ) {
		return esc_html( gsley_site( 'location' ) );
	}
	$has_addr = false;
	foreach ( $offices as $o ) {
		$has_addr = $has_addr || '' !== trim( $o['street'] );
	}
	if ( ! $has_addr ) {
		return esc_html( gsley_site( 'location' ) );
	}
	$html = '<div class="offices">';
	foreach ( $offices as $o ) {
		$html .= '<div class="office"><span class="office__city">' . esc_html( $o['city'] ) . '</span>';
		if ( '' !== trim( $o['street'] ) ) {
			$html .= '<span class="office__addr">' . esc_html( trim( $o['street'] . ( $o['postcode'] ? ', ' . $o['postcode'] : '' ) ) ) . '</span>';
		}
		if ( $o['phone'] ) {
			$html .= '<a class="office__phone" href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $o['phone'] ) ) . '">' . esc_html( $o['phone'] ) . '</a>';
		}
		if ( $o['maps'] ) {
			$html .= '<a class="open-link" href="' . esc_url( $o['maps'] ) . '" target="_blank" rel="noopener">Get directions ↗</a>';
		}
		$html .= '</div>';
	}
	return $html . '</div>';
}

/** [gsley_booking] — the scheduler section on Contact; empty until a booking URL is set. */
function gsley_sc_booking() {
	$url = gsley_site( 'booking_url' );
	if ( ! $url ) {
		return '';
	}
	return '<section class="section section--bone" id="book"><div class="wrap">'
		. '<h2 class="h2 reveal" style="margin-bottom:var(--sp-8)">30 minutes. Pick a time that suits you.</h2>'
		. '<div class="booking reveal"><iframe src="' . esc_url( $url ) . '" title="Book a discovery call with GSLEY" loading="lazy"></iframe></div>'
		. '</div></section>';
}

/** WhatsApp glyph used by the FAB, action bar and mobile menu. */
function gsley_wa_icon( $size = 26 ) {
	return '<svg viewBox="0 0 32 32" width="' . (int) $size . '" height="' . (int) $size . '" fill="currentColor" aria-hidden="true"><path d="M16.04 3C9.4 3 4 8.4 4 15.04c0 2.12.55 4.16 1.6 5.98L4 29l8.2-1.55a12 12 0 0 0 3.84.63h.01c6.63 0 12.03-5.4 12.03-12.04C28.08 8.4 22.68 3 16.04 3zm0 21.9c-1.2 0-2.39-.2-3.51-.6l-.25-.1-4.87.92.93-4.74-.16-.26a9.86 9.86 0 0 1-1.51-5.22c0-5.46 4.45-9.9 9.92-9.9 2.65 0 5.13 1.03 7 2.9a9.82 9.82 0 0 1 2.9 7c0 5.47-4.45 9.9-9.92 9.9zm5.44-7.4c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>';
}

/**
 * [gsley_actions] — the desktop WhatsApp button and the mobile action bar.
 * Contact and Health put "Book a Discovery Call" first; everywhere else, the Growth Diagnostic.
 */
function gsley_sc_actions() {
	$wa   = gsley_site( 'whatsapp_url' );
	$book = is_page( 'contact' ) || is_page( 'health' );
	$ext  = gsley_site( 'booking_url' ) ? ' target="_blank" rel="noopener"' : '';
	$primary = $book
		? '<a href="' . esc_url( gsley_site( 'booking_href' ) ) . '"' . $ext . ' class="btn btn--solid">Book a Discovery Call ↗</a>'
		: '<a href="' . esc_url( home_url( '/tools/growth-diagnostic/' ) ) . '" class="btn btn--solid">Run the Growth Diagnostic ↗</a>';
	return '<a class="wa-fab" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener" aria-label="Chat with GSLEY on WhatsApp">' . gsley_wa_icon() . '</a>'
		. '<div class="action-bar" aria-label="Quick actions">' . $primary
		. '<a href="' . esc_url( $wa ) . '" target="_blank" rel="noopener" class="btn btn--wa" aria-label="Chat on WhatsApp">' . gsley_wa_icon() . '</a></div>';
}

/* ============================================================ DATA-BIND RESOLUTION
   Theme markup carries working defaults plus a data-bind / data-count
   attribute, e.g. <a href="/contact/#brief" data-bind="book">. These are
   resolved on the server against GSLEY Settings and the registry, so the
   served HTML is always current and nothing depends on JavaScript. */

add_filter( 'render_block', 'gsley_render_block_bindings', 20, 2 );
function gsley_render_block_bindings( $html, $block ) {
	if ( ! is_string( $html ) ) {
		return $html;
	}
	// GSLEY shortcodes inside patterns that templates reference are not reached by
	// WordPress's own do_shortcode pass (patterns expand after it), so run it here.
	if ( false !== strpos( $html, '[gsley' ) ) {
		$html = do_shortcode( $html );
	}
	if ( false === strpos( $html, 'data-bind=' ) && false === strpos( $html, 'data-count=' ) ) {
		return $html;
	}
	return gsley_resolve_bindings( $html );
}

/** Replace an attribute value in an opening-tag string (adds it if absent). */
function gsley_set_attr( $tag, $name, $value ) {
	$attr = $name . '="' . esc_attr( $value ) . '"';
	if ( preg_match( '/\s' . preg_quote( $name, '/' ) . '="[^"]*"/', $tag ) ) {
		return preg_replace( '/\s' . preg_quote( $name, '/' ) . '="[^"]*"/', ' ' . $attr, $tag, 1 );
	}
	return preg_replace( '/^<([a-z0-9]+)/i', '<$1 ' . $attr, $tag, 1 );
}

function gsley_resolve_bindings( $html ) {
	// data-count="…" [data-cap] [data-format="number"].
	$html = preg_replace_callback(
		'#(<(span|strong|b|em)\b[^>]*\bdata-count="([a-z_-]+)"[^>]*>)([^<]*)(</\2>)#i',
		function ( $m ) {
			$cap    = (bool) preg_match( '/\sdata-cap\b/', $m[1] );
			$number = (bool) preg_match( '/\sdata-format="number"/', $m[1] );
			$n      = gsley_count( $m[3] );
			return $m[1] . esc_html( $number ? (string) $n : gsley_number_word( $n, $cap ) ) . $m[5];
		},
		$html
	);

	// data-bind="email|phone|whatsapp|book|location|hours".
	return preg_replace_callback(
		'#(<(a|span|div|p)\b[^>]*\bdata-bind="([a-z_]+)"[^>]*>)(.*?)(</\2>)#is',
		function ( $m ) {
			$open  = $m[1];
			$key   = $m[3];
			$inner = $m[4];
			$plain = ! preg_match( '/<[a-z]/i', $inner );
			$text  = trim( wp_strip_all_tags( $inner ) );
			$is_a  = 'a' === strtolower( $m[2] );
			switch ( $key ) {
				case 'email':
					if ( $is_a ) {
						$open = gsley_set_attr( $open, 'href', gsley_site( 'email_href' ) );
					}
					if ( $plain && false !== strpos( $text, '@' ) ) {
						$inner = esc_html( gsley_site( 'email' ) );
					}
					break;
				case 'phone':
					if ( $is_a ) {
						$open = gsley_set_attr( $open, 'href', gsley_site( 'phone_href' ) );
					}
					if ( $plain && preg_match( '/^[+\d][\d\s().-]+$/', $text ) ) {
						$inner = esc_html( gsley_site( 'phone' ) );
					}
					break;
				case 'whatsapp':
					if ( $is_a ) {
						$open = gsley_set_attr( gsley_set_attr( gsley_set_attr( $open, 'href', gsley_site( 'whatsapp_url' ) ), 'target', '_blank' ), 'rel', 'noopener' );
					}
					break;
				case 'book':
					if ( $is_a ) {
						$open = gsley_set_attr( $open, 'href', gsley_site( 'booking_href' ) );
						if ( gsley_site( 'booking_url' ) ) {
							$open = gsley_set_attr( gsley_set_attr( $open, 'target', '_blank' ), 'rel', 'noopener' );
						}
					}
					break;
				case 'location':
				case 'hours':
				case 'tagline':
				case 'year':
					if ( $plain ) {
						$inner = esc_html( gsley_site( $key ) );
					}
					break;
			}
			return $open . $inner . $m[5];
		},
		$html
	);
}

/* ============================================================ BLOCK BINDINGS
   Lets an admin bind a Paragraph / Heading / Button to a site fact in the
   editor: {"metadata":{"bindings":{"content":{"source":"gsley/site","args":{"key":"email"}}}}} */

add_action( 'init', 'gsley_register_bindings_source' );
function gsley_register_bindings_source() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}
	register_block_bindings_source(
		'gsley/site',
		array(
			'label'              => __( 'GSLEY Settings', 'gsley-diagnostics' ),
			'get_value_callback' => function ( $args ) {
				$key = isset( $args['key'] ) ? sanitize_key( $args['key'] ) : '';
				if ( isset( $args['count'] ) ) {
					$n = gsley_count( $args['count'] );
					return isset( $args['format'] ) && 'number' === $args['format'] ? (string) $n : gsley_number_word( $n, ! empty( $args['cap'] ) );
				}
				return gsley_site( $key );
			},
		)
	);
}
