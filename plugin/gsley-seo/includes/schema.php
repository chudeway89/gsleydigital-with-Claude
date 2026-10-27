<?php
/**
 * GSLEY SEO — JSON-LD schema (phase1-foundation.md §4 / handoff §5).
 *
 * @package GSLEY_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'gsley_seo_output_schema', 5 );
function gsley_seo_output_schema() {
	if ( is_admin() ) {
		return;
	}
	$pt    = gsley_seo_page_type();
	$graph = array();

	// Site-wide: Organization + WebSite.
	$graph[] = gsley_seo_organization_node();
	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => 'GSLEY Digital',
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$canonical = gsley_seo_canonical( $pt );
	$desc      = gsley_seo_description( $pt );

	switch ( $pt['type'] ) {
		case 'home':
			$items = array();
			$market = gsley_seo_market_services();
			foreach ( array( 'website-design', 'google-ads', 'email-marketing', 'social-media-marketing', 'content-development' ) as $i => $slug ) {
				$items[] = gsley_seo_listitem( $i + 1, $market[ $slug ], trailingslashit( home_url( '/services/market/' . $slug ) ) );
			}
			$items[] = gsley_seo_listitem( 6, 'AI Agent Deployment', home_url( '/services/digital/ai-agent-deployment/' ) );
			$graph[] = array( '@type' => 'ItemList', 'name' => 'Start here', 'itemListElement' => $items );
			$graph[] = gsley_seo_faqpage( gsley_seo_home_faq() );
			break;

		case 'about':
			$graph[] = array(
				'@type'    => 'Person',
				'name'     => 'Chukwudi Kingsley Eze',
				'honorificSuffix' => 'MCIPR',
				'jobTitle' => 'Founder',
				'worksFor' => array( '@id' => home_url( '/#organization' ) ),
				'url'      => home_url( '/about/' ),
				'sameAs'   => 'https://www.linkedin.com/in/chukwudihex',
			);
			$graph[] = gsley_seo_breadcrumblist( $pt );
			break;

		case 'contact':
			$graph[] = array(
				'@type'    => 'ContactPage',
				'url'      => $canonical,
				'name'     => 'Contact GSLEY Digital',
				'about'    => array( '@id' => home_url( '/#organization' ) ),
			);
			$graph[] = gsley_seo_breadcrumblist( $pt );
			break;

		case 'services-index':
		case 'pillar':
		case 'tools-hub':
			$graph[] = array(
				'@type'    => 'CollectionPage',
				'url'      => $canonical,
				'name'     => gsley_seo_doc_title(),
				'isPartOf' => array( '@id' => home_url( '/#website' ) ),
			);
			$graph[] = gsley_seo_collection_itemlist( $pt );
			$graph[] = gsley_seo_breadcrumblist( $pt );
			break;

		case 'service':
			$graph[] = array(
				'@type'       => 'Service',
				'serviceType' => $pt['name'],
				'name'        => $pt['name'],
				'description' => $desc,
				'url'         => $canonical,
				'provider'    => array( '@id' => home_url( '/#organization' ) ),
				'areaServed'  => array( 'Nigeria', 'Worldwide' ),
				'hasOfferCatalog' => array(
					'@type' => 'OfferCatalog',
					'name'  => 'Engagement tiers',
					'itemListElement' => array(
						array( '@type' => 'Offer', 'name' => 'Toolkit', 'description' => 'Productised playbooks and operating systems.' ),
						array( '@type' => 'Offer', 'name' => 'Sprint', 'description' => 'A fixed-scope productised engagement.' ),
						array( '@type' => 'Offer', 'name' => 'Retainer', 'description' => 'Ongoing advisory and fractional executive engagement.' ),
					),
				),
			);
			$faq = gsley_seo_faq_from_post();
			if ( $faq ) {
				$graph[] = gsley_seo_faqpage( $faq );
			}
			$graph[] = gsley_seo_breadcrumblist( $pt );
			break;

		case 'tool':
			$graph[] = array(
				'@type'           => 'SoftwareApplication',
				'name'            => $pt['name'],
				'applicationCategory' => 'BusinessApplication',
				'operatingSystem' => 'Web',
				'url'             => $canonical,
				'description'     => $desc,
				'offers'          => array( '@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'NGN' ),
				'provider'        => array( '@id' => home_url( '/#organization' ) ),
			);
			$graph[] = array(
				'@type' => 'HowTo',
				'name'  => 'How the ' . $pt['name'] . ' works',
				'step'  => array(
					array( '@type' => 'HowToStep', 'position' => 1, 'name' => 'Answer the questions', 'text' => 'Complete a short set of questions — no sign-up required to begin.' ),
					array( '@type' => 'HowToStep', 'position' => 2, 'name' => 'See your scored read-out', 'text' => 'Get an instant score, interpretation, and one clear recommendation.' ),
					array( '@type' => 'HowToStep', 'position' => 3, 'name' => 'Get the full report', 'text' => 'Receive a detailed report and a sequenced action plan by email.' ),
				),
			);
			$faq = gsley_seo_faq_from_post();
			if ( $faq ) {
				$graph[] = gsley_seo_faqpage( $faq );
			}
			$graph[] = gsley_seo_breadcrumblist( $pt );
			break;

		case 'legal':
			$graph[] = array( '@type' => 'WebPage', 'url' => $canonical, 'name' => $pt['name'], 'isPartOf' => array( '@id' => home_url( '/#website' ) ) );
			$graph[] = gsley_seo_breadcrumblist( $pt );
			break;
	}

	$doc = array( '@context' => 'https://schema.org', '@graph' => $graph );
	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $doc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}

function gsley_seo_listitem( $pos, $name, $url ) {
	return array( '@type' => 'ListItem', 'position' => $pos, 'name' => $name, 'item' => $url );
}

function gsley_seo_collection_itemlist( $pt ) {
	$items = array();
	$i = 1;
	if ( 'pillar' === $pt['type'] ) {
		$svc = 'market' === $pt['pillar'] ? gsley_seo_market_services() : gsley_seo_digital_services();
		foreach ( $svc as $slug => $name ) {
			$items[] = gsley_seo_listitem( $i++, $name, trailingslashit( home_url( '/services/' . $pt['pillar'] . '/' . $slug ) ) );
		}
	} elseif ( 'tools-hub' === $pt['type'] ) {
		foreach ( gsley_seo_tools() as $slug => $name ) {
			$items[] = gsley_seo_listitem( $i++, $name, trailingslashit( home_url( '/tools/' . $slug ) ) );
		}
	} else { // services index
		$items[] = gsley_seo_listitem( $i++, 'GSLEY Market', home_url( '/services/market/' ) );
		$items[] = gsley_seo_listitem( $i++, 'GSLEY Digital', home_url( '/services/digital/' ) );
	}
	return array( '@type' => 'ItemList', 'itemListElement' => $items );
}

function gsley_seo_breadcrumblist( $pt ) {
	$items = array();
	$i = 1;
	foreach ( gsley_seo_breadcrumbs( $pt ) as $c ) {
		$items[] = array( '@type' => 'ListItem', 'position' => $i++, 'name' => $c[0], 'item' => $c[1] );
	}
	return array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
}

function gsley_seo_faqpage( $pairs ) {
	$q = array();
	foreach ( $pairs as $p ) {
		$q[] = array(
			'@type'          => 'Question',
			'name'           => $p[0],
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $p[1] ),
		);
	}
	return array( '@type' => 'FAQPage', 'mainEntity' => $q );
}

/** Extract real FAQ pairs from the current page's core/details blocks. */
function gsley_seo_faq_from_post() {
	if ( ! is_singular() ) {
		return array();
	}
	$post = get_queried_object();
	if ( ! $post || empty( $post->post_content ) || false === strpos( $post->post_content, 'wp-block-details' ) ) {
		return array();
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return array();
	}
	$faqs = array();
	libxml_use_internal_errors( true );
	$doc = new DOMDocument();
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $post->post_content );
	libxml_clear_errors();
	foreach ( $doc->getElementsByTagName( 'details' ) as $d ) {
		$sum = $d->getElementsByTagName( 'summary' )->item( 0 );
		if ( ! $sum ) {
			continue;
		}
		$question = trim( $sum->textContent );
		$answer   = '';
		foreach ( $d->childNodes as $c ) {
			if ( XML_ELEMENT_NODE === $c->nodeType && 'summary' !== strtolower( $c->nodeName ) ) {
				$answer = trim( $c->textContent );
				if ( '' !== $answer ) {
					break;
				}
			}
		}
		if ( '' !== $question && '' !== $answer ) {
			$faqs[] = array( $question, $answer );
		}
	}
	return $faqs;
}
