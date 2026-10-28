<?php
/**
 * GSLEY Diagnostics — audit engine (owned, server-side; no paid APIs).
 *
 * Honesty rules:
 *  - Every scored item is a check we actually performed on the live target, or
 *    a value the user explicitly supplied (labelled as such). Nothing invented.
 *  - Signals we cannot read server-side (e.g. follower counts behind login
 *    walls, broad web mentions) are taken as optional user input or reported as
 *    "not automatically verifiable" — never guessed.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Normalise a user-supplied URL; '' if not usable. */
function gsley_audit_norm_url( $u ) {
	$u = trim( (string) $u );
	if ( '' === $u ) {
		return '';
	}
	if ( ! preg_match( '#^https?://#i', $u ) ) {
		$u = 'https://' . $u;
	}
	if ( strlen( $u ) > 300 ) {
		return '';
	}
	$u = esc_url_raw( $u, array( 'http', 'https' ) );
	return ( $u && wp_http_validate_url( $u ) ) ? $u : '';
}

/** Fetch a URL. Returns { ok, code, body, error }. Public hosts only — see gsley_safe_fetch(). */
function gsley_audit_fetch( $url ) {
	return gsley_safe_fetch( $url );
}

/** Build a DOMXPath from HTML (size-capped). */
function gsley_audit_xpath( $html ) {
	if ( ! class_exists( 'DOMDocument' ) || '' === trim( (string) $html ) ) {
		return null;
	}
	$html = substr( $html, 0, 600000 );
	libxml_use_internal_errors( true );
	$doc = new DOMDocument();
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
	libxml_clear_errors();
	return new DOMXPath( $doc );
}

function gsley_audit_check( $id, $label, $status, $detail, $weight ) {
	return array( 'id' => $id, 'label' => $label, 'status' => $status, 'detail' => $detail, 'weight' => (int) $weight );
}

/** Score a set of checks → percent (pass=full, warn=half, fail/na handling). */
function gsley_audit_score( $checks ) {
	$earned = 0;
	$total  = 0;
	foreach ( $checks as $c ) {
		if ( 'na' === $c['status'] ) {
			continue;
		}
		$total += $c['weight'];
		if ( 'pass' === $c['status'] ) {
			$earned += $c['weight'];
		} elseif ( 'warn' === $c['status'] ) {
			$earned += $c['weight'] * 0.5;
		}
	}
	return $total > 0 ? (int) round( $earned / $total * 100 ) : 0;
}

/**
 * Analyse a website. Returns { ok, checks[], signals{}, error }.
 * signals: title, social_links[], has_jsonld, has_review_schema, host
 */
function gsley_audit_website( $url ) {
	$res = gsley_audit_fetch( $url );
	$checks = array();
	if ( ! $res['ok'] || $res['code'] >= 400 || '' === $res['body'] ) {
		$checks[] = gsley_audit_check( 'reachable', 'Website reachable', 'fail', $res['code'] ? ( 'HTTP ' . $res['code'] ) : ( $res['error'] ? $res['error'] : 'Could not connect' ), 5 );
		return array( 'ok' => false, 'checks' => $checks, 'signals' => array(), 'error' => 'Could not load the website.' );
	}
	$checks[] = gsley_audit_check( 'reachable', 'Website reachable', 'pass', 'Loaded (HTTP ' . $res['code'] . ')', 5 );
	$checks[] = gsley_audit_check( 'https', 'Served over HTTPS', ( 0 === strpos( $url, 'https://' ) ) ? 'pass' : 'fail', ( 0 === strpos( $url, 'https://' ) ) ? 'Secure connection' : 'No HTTPS', 5 );

	$xp = gsley_audit_xpath( $res['body'] );
	$signals = array( 'title' => '', 'social_links' => array(), 'has_jsonld' => false, 'has_review_schema' => false, 'host' => wp_parse_url( $url, PHP_URL_HOST ) );

	if ( ! $xp ) {
		$checks[] = gsley_audit_check( 'parse', 'HTML parseable', 'warn', 'Could not parse markup', 1 );
		return array( 'ok' => true, 'checks' => $checks, 'signals' => $signals, 'error' => '' );
	}

	// Title.
	$title = $xp->query( '//title' );
	$tt    = $title->length ? trim( $title->item( 0 )->textContent ) : '';
	$signals['title'] = $tt;
	$tl    = function_exists( 'mb_strlen' ) ? mb_strlen( $tt ) : strlen( $tt );
	$checks[] = '' === $tt
		? gsley_audit_check( 'title', 'Page title', 'fail', 'No <title>', 4 )
		: gsley_audit_check( 'title', 'Page title', ( $tl >= 10 && $tl <= 65 ) ? 'pass' : 'warn', $tl . ' chars: "' . wp_trim_words( $tt, 12 ) . '"', 4 );

	// Meta description.
	$md = $xp->query( '//meta[@name="description"]/@content' );
	$mdv = $md->length ? trim( $md->item( 0 )->nodeValue ) : '';
	$ml = function_exists( 'mb_strlen' ) ? mb_strlen( $mdv ) : strlen( $mdv );
	$checks[] = '' === $mdv
		? gsley_audit_check( 'meta_desc', 'Meta description', 'fail', 'Missing', 4 )
		: gsley_audit_check( 'meta_desc', 'Meta description', ( $ml >= 50 && $ml <= 160 ) ? 'pass' : 'warn', $ml . ' chars', 4 );

	// Canonical.
	$can = $xp->query( '//link[@rel="canonical"]' );
	$checks[] = gsley_audit_check( 'canonical', 'Canonical tag', $can->length ? 'pass' : 'warn', $can->length ? 'Present' : 'Missing', 2 );

	// H1.
	$h1 = $xp->query( '//h1' );
	$checks[] = 0 === $h1->length
		? gsley_audit_check( 'h1', 'Single H1 heading', 'fail', 'No H1 found', 3 )
		: gsley_audit_check( 'h1', 'Single H1 heading', ( 1 === $h1->length ) ? 'pass' : 'warn', $h1->length . ' H1 tags', 3 );

	// Viewport (mobile).
	$vp = $xp->query( '//meta[@name="viewport"]' );
	$checks[] = gsley_audit_check( 'viewport', 'Mobile viewport', $vp->length ? 'pass' : 'fail', $vp->length ? 'Responsive meta present' : 'No viewport meta', 4 );

	// Image alt coverage.
	$imgs = $xp->query( '//img' );
	$total_img = $imgs->length;
	$with_alt  = 0;
	foreach ( $imgs as $im ) {
		if ( $im->hasAttribute( 'alt' ) && '' !== trim( $im->getAttribute( 'alt' ) ) ) {
			$with_alt++;
		}
	}
	if ( 0 === $total_img ) {
		$checks[] = gsley_audit_check( 'alt', 'Image alt text', 'na', 'No images found', 2 );
	} else {
		$ratio = $with_alt / $total_img;
		$st    = $ratio >= 0.8 ? 'pass' : ( $ratio >= 0.5 ? 'warn' : 'fail' );
		$checks[] = gsley_audit_check( 'alt', 'Image alt text', $st, $with_alt . ' of ' . $total_img . ' images have alt text', 2 );
	}

	// Open Graph.
	$og = $xp->query( '//meta[@property="og:title"] | //meta[@property="og:image"]' );
	$checks[] = gsley_audit_check( 'og', 'Social share (Open Graph) tags', $og->length >= 2 ? 'pass' : ( $og->length ? 'warn' : 'warn' ), $og->length ? 'Present' : 'Missing', 2 );

	// Structured data.
	$ld = $xp->query( '//script[@type="application/ld+json"]' );
	$signals['has_jsonld'] = $ld->length > 0;
	$ldraw = '';
	foreach ( $ld as $n ) {
		$ldraw .= ' ' . $n->textContent;
	}
	$signals['has_review_schema'] = ( false !== stripos( $ldraw, 'aggregateRating' ) || false !== stripos( $ldraw, '"review"' ) );
	$checks[] = gsley_audit_check( 'schema', 'Structured data (Schema.org)', $ld->length ? 'pass' : 'warn', $ld->length ? $ld->length . ' JSON-LD block(s)' : 'None found', 3 );

	// Lang.
	$lang = $xp->query( '//html/@lang' );
	$checks[] = gsley_audit_check( 'lang', 'Language declared', ( $lang->length && trim( $lang->item( 0 )->nodeValue ) ) ? 'pass' : 'warn', $lang->length ? trim( $lang->item( 0 )->nodeValue ) : 'No lang attribute', 1 );

	// Favicon.
	$fav = $xp->query( '//link[contains(@rel,"icon")]' );
	$checks[] = gsley_audit_check( 'favicon', 'Favicon', $fav->length ? 'pass' : 'warn', $fav->length ? 'Present' : 'Missing', 1 );

	// Page weight (HTML only — a lightweight signal, not full Core Web Vitals).
	$bytes = strlen( $res['body'] );
	$kb    = round( $bytes / 1024 );
	$st    = $bytes < 150000 ? 'pass' : ( $bytes < 400000 ? 'warn' : 'fail' );
	$checks[] = gsley_audit_check( 'weight', 'HTML page weight', $st, $kb . ' KB of HTML', 2 );

	// Social links on the page.
	$signals['social_links'] = gsley_audit_social_links_in( $xp );

	// robots.txt + sitemap.xml.
	$base = ( 0 === strpos( $url, 'https://' ) ? 'https://' : 'http://' ) . $signals['host'];
	$rob  = gsley_audit_fetch( $base . '/robots.txt' );
	$checks[] = gsley_audit_check( 'robots', 'robots.txt', ( $rob['ok'] && $rob['code'] < 400 && '' !== trim( $rob['body'] ) ) ? 'pass' : 'warn', ( $rob['ok'] && $rob['code'] < 400 ) ? 'Found' : 'Not found', 2 );
	$has_sitemap = ( $rob['ok'] && false !== stripos( $rob['body'], 'sitemap:' ) );
	if ( ! $has_sitemap ) {
		$sm = gsley_audit_fetch( $base . '/sitemap.xml' );
		$has_sitemap = ( $sm['ok'] && $sm['code'] < 400 && false !== stripos( $sm['body'], '<urlset' ) || ( isset( $sm['body'] ) && false !== stripos( $sm['body'], '<sitemapindex' ) ) );
	}
	$checks[] = gsley_audit_check( 'sitemap', 'XML sitemap', $has_sitemap ? 'pass' : 'warn', $has_sitemap ? 'Found' : 'Not found', 2 );

	return array( 'ok' => true, 'checks' => $checks, 'signals' => $signals, 'error' => '' );
}

/** Find outbound social links on a page. Returns map platform=>url. */
function gsley_audit_social_links_in( $xp ) {
	$platforms = array(
		'facebook'  => array( 'facebook.com', 'fb.com' ),
		'instagram' => array( 'instagram.com' ),
		'x'         => array( 'twitter.com', 'x.com' ),
		'linkedin'  => array( 'linkedin.com' ),
		'youtube'   => array( 'youtube.com', 'youtu.be' ),
		'tiktok'    => array( 'tiktok.com' ),
	);
	$found = array();
	$links = $xp->query( '//a/@href' );
	foreach ( $links as $href ) {
		$u = $href->nodeValue;
		foreach ( $platforms as $key => $domains ) {
			if ( isset( $found[ $key ] ) ) {
				continue;
			}
			foreach ( $domains as $d ) {
				if ( false !== stripos( $u, $d ) ) {
					$found[ $key ] = $u;
					break;
				}
			}
		}
	}
	return $found;
}

/**
 * Check a public social profile URL. Honest about what's verifiable server-side.
 * Returns { platform, url, status(pass|warn|fail), detail }.
 */
function gsley_audit_social_profile( $platform, $url ) {
	$res = gsley_audit_fetch( $url );
	if ( ! $res['ok'] || 0 === $res['code'] ) {
		return array( 'platform' => $platform, 'url' => $url, 'status' => 'fail', 'detail' => 'Could not reach the profile' );
	}
	if ( $res['code'] >= 400 ) {
		return array( 'platform' => $platform, 'url' => $url, 'status' => 'fail', 'detail' => 'Profile not found (HTTP ' . $res['code'] . ')' );
	}
	// Public og:title is a good signal of a real, public profile page.
	$xp = gsley_audit_xpath( $res['body'] );
	$has_og = $xp ? ( $xp->query( '//meta[@property="og:title"]' )->length > 0 ) : false;
	if ( $has_og ) {
		return array( 'platform' => $platform, 'url' => $url, 'status' => 'pass', 'detail' => 'Public profile reachable' );
	}
	// Reachable but likely gated to logged-out server requests (e.g. Instagram/X).
	return array( 'platform' => $platform, 'url' => $url, 'status' => 'warn', 'detail' => 'Reachable, but profile details are not publicly readable to verify automatically' );
}

/** Platform label. */
function gsley_audit_platform_label( $key ) {
	$m = array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok' );
	return isset( $m[ $key ] ) ? $m[ $key ] : ucfirst( $key );
}

/** Detect platform from a profile URL. */
function gsley_audit_detect_platform( $url ) {
	$map = array( 'facebook.com' => 'facebook', 'fb.com' => 'facebook', 'instagram.com' => 'instagram', 'twitter.com' => 'x', 'x.com' => 'x', 'linkedin.com' => 'linkedin', 'youtube.com' => 'youtube', 'youtu.be' => 'youtube', 'tiktok.com' => 'tiktok' );
	foreach ( $map as $d => $k ) {
		if ( false !== stripos( $url, $d ) ) {
			return $k;
		}
	}
	return 'other';
}
