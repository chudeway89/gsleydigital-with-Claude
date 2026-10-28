<?php
/**
 * GSLEY Diagnostics — audit orchestration + rule-based recommendations.
 *
 * Recommendations are derived only from real findings (a check that warned or
 * failed). No invented benchmarks.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** A fix for a given check id. */
function gsley_audit_reco( $id, $detail ) {
	$base = preg_replace( '/^social_/', 'social_', $id );
	$map  = array(
		'reachable'  => array( 'Make the site reliably reachable', 'Your site did not load cleanly for our crawler. Check uptime, DNS, and server response.' ),
		'https'      => array( 'Serve the whole site over HTTPS', 'Install/renew an SSL certificate and force https:// site-wide. It is a ranking and trust signal.' ),
		'title'      => array( 'Write a focused page title', 'Aim for a unique 10–65 character title with your primary keyword and brand.' ),
		'meta_desc'  => array( 'Add a compelling meta description', 'Write a 50–160 character description per page; it drives click-through from search and social.' ),
		'canonical'  => array( 'Add canonical tags', 'Declare a self-referencing canonical URL on each page to avoid duplicate-content dilution.' ),
		'h1'         => array( 'Use exactly one clear H1', 'Each page should have a single H1 that states its purpose; use H2/H3 for structure.' ),
		'viewport'   => array( 'Make the site mobile-responsive', 'Add a responsive viewport meta tag and verify the layout on phones — most visitors are mobile.' ),
		'alt'        => array( 'Add alt text to images', 'Describe images for accessibility and image SEO; decorative images can use empty alt.' ),
		'og'         => array( 'Add social-share (Open Graph) tags', 'Add og:title, og:description and og:image so shared links render with a proper preview.' ),
		'schema'     => array( 'Add structured data', 'Add Schema.org JSON-LD (Organization, LocalBusiness, Service, FAQ) to win rich results and AI citations.' ),
		'lang'       => array( 'Declare the page language', 'Set <html lang="…"> for accessibility and correct indexing.' ),
		'favicon'    => array( 'Add a favicon', 'A favicon improves recognition in tabs, bookmarks, and search.' ),
		'weight'     => array( 'Reduce page weight', 'Compress and lazy-load images, defer non-critical scripts, and enable caching to speed up load.' ),
		'robots'     => array( 'Publish a robots.txt', 'Add a robots.txt that allows crawling and points to your sitemap.' ),
		'sitemap'    => array( 'Publish an XML sitemap', 'Generate an XML sitemap and reference it in robots.txt and Search Console.' ),
		'integration'=> array( 'Link your socials from your website', 'Add your social profile links to the header/footer so visitors and crawlers connect your presence.' ),
		'consistency'=> array( 'Use one consistent handle', 'Align your username across platforms so your brand is easy to find and verify.' ),
		'rep_schema' => array( 'Surface your ratings in search', 'Add review/aggregateRating structured data so star ratings can appear in search results.' ),
		'google_rating' => array( 'Lift your Google rating', 'Systematically request reviews from happy customers and respond to every review to raise your average.' ),
		'google_reviews'=> array( 'Grow your review volume', 'Run an ongoing review-generation flow (post-purchase email/WhatsApp) to build credible volume.' ),
		'no_social'  => array( 'Establish your social presence', 'Claim and complete profiles on the platforms your buyers use, with consistent branding.' ),
	);
	if ( 0 === strpos( $id, 'social_' ) ) {
		$plat = gsley_audit_platform_label( substr( $id, 7 ) );
		return array( 'Complete your ' . $plat . ' presence', 'Claim/activate and fully complete your ' . $plat . ' profile (bio, link, branding) and post consistently.' );
	}
	return isset( $map[ $id ] ) ? $map[ $id ] : array( 'Improve: ' . $id, $detail );
}

/** Build recommendations from checks that warned/failed. */
function gsley_audit_recommendations( $checks ) {
	$recs = array();
	foreach ( $checks as $c ) {
		if ( 'warn' === $c['status'] || 'fail' === $c['status'] ) {
			$r = gsley_audit_reco( $c['id'], $c['detail'] );
			$recs[] = array(
				'priority' => 'fail' === $c['status'] ? 'high' : 'medium',
				'title'    => $r[0],
				'detail'   => $r[1],
				'evidence' => $c['label'] . ' — ' . $c['detail'],
			);
		}
	}
	// High priority first.
	usort( $recs, function ( $a, $b ) {
		$o = array( 'high' => 0, 'medium' => 1, 'low' => 2 );
		return $o[ $a['priority'] ] <=> $o[ $b['priority'] ];
	} );
	return $recs;
}

/** Collect social profiles from inputs + site links. Returns [ [platform,url], ... ]. */
function gsley_audit_collect_profiles( $socials, $site ) {
	$profiles = array();
	$seen     = array();
	foreach ( (array) $socials as $u ) {
		$u = gsley_audit_norm_url( $u );
		if ( '' === $u ) {
			continue;
		}
		$p = gsley_audit_detect_platform( $u );
		$profiles[] = array( $p, $u );
		$seen[ $p ] = true;
	}
	if ( $site && ! empty( $site['signals']['social_links'] ) ) {
		foreach ( $site['signals']['social_links'] as $p => $u ) {
			if ( empty( $seen[ $p ] ) ) {
				$profiles[] = array( $p, $u );
				$seen[ $p ] = true;
			}
		}
	}
	return $profiles;
}

/** Social presence checks from profiles. */
function gsley_audit_social_checks( $profiles ) {
	$checks = array();
	if ( empty( $profiles ) ) {
		$checks[] = gsley_audit_check( 'no_social', 'Social profiles', 'fail', 'No social profiles provided or found on the website', 3 );
		return $checks;
	}
	foreach ( $profiles as $pr ) {
		list( $plat, $url ) = $pr;
		$r = gsley_audit_social_profile( $plat, $url );
		$checks[] = gsley_audit_check( 'social_' . $plat, gsley_audit_platform_label( $plat ) . ' profile', $r['status'], $r['detail'], 3 );
	}
	return $checks;
}

/**
 * Run an audit.
 *
 * @param string $tool   social-media-audit | digital-presence-orm-audit
 * @param array  $inputs website, socials[], google_rating, google_reviews
 * @return array { overall, pillars[], findings[], summary, recommendations[] }
 */
function gsley_audit_run( $tool, $inputs ) {
	$website = gsley_audit_norm_url( isset( $inputs['website'] ) ? $inputs['website'] : '' );
	$socials = isset( $inputs['socials'] ) ? (array) $inputs['socials'] : array();
	$site    = $website ? gsley_audit_website( $website ) : null;

	$pillars = array();
	$findings = array();
	$all_checks = array();

	if ( 'social-media-audit' === $tool ) {
		$profiles = gsley_audit_collect_profiles( $socials, $site );
		$presence = gsley_audit_social_checks( $profiles );

		// Integration: does the website link to socials?
		if ( $site && $site['ok'] ) {
			$n = count( $site['signals']['social_links'] );
			$integration = gsley_audit_check( 'integration', 'Socials linked from website', $n >= 2 ? 'pass' : ( 1 === $n ? 'warn' : 'fail' ), $n . ' social link(s) found on the site', 3 );
		} else {
			$integration = gsley_audit_check( 'integration', 'Socials linked from website', 'na', 'No website provided', 3 );
		}

		// Consistency of handle.
		$handles = array();
		foreach ( $profiles as $pr ) {
			$path = trim( (string) wp_parse_url( $pr[1], PHP_URL_PATH ), '/' );
			$seg  = explode( '/', $path );
			$h    = strtolower( preg_replace( '/^@/', '', end( $seg ) ) );
			if ( $h ) {
				$handles[ $h ] = true;
			}
		}
		if ( count( $profiles ) > 1 ) {
			$consistency = gsley_audit_check( 'consistency', 'Consistent handle across platforms', count( $handles ) <= 1 ? 'pass' : 'warn', count( $handles ) <= 1 ? 'Same handle used' : 'Different handles across platforms', 2 );
		} else {
			$consistency = gsley_audit_check( 'consistency', 'Consistent handle across platforms', 'na', 'Need 2+ profiles to assess', 2 );
		}

		$pillars[] = array( 'id' => 'presence', 'label' => 'Presence', 'pct' => gsley_audit_score( $presence ) );
		$pillars[] = array( 'id' => 'integration', 'label' => 'Website integration', 'pct' => gsley_audit_score( array( $integration ) ) );
		$pillars[] = array( 'id' => 'consistency', 'label' => 'Consistency', 'pct' => gsley_audit_score( array( $consistency ) ) );
		$all_checks = array_merge( $presence, array( $integration, $consistency ) );
		$findings   = $all_checks;
	} else { // digital-presence-orm-audit
		if ( ! $site || ! $site['ok'] ) {
			// Website is the backbone; still produce a partial, honest result.
			$site = $site ? $site : array( 'ok' => false, 'checks' => array(), 'signals' => array( 'social_links' => array(), 'has_review_schema' => false ), 'error' => 'No website provided' );
		}
		$seo_ids  = array( 'title', 'meta_desc', 'canonical', 'h1', 'schema', 'sitemap', 'robots', 'lang' );
		$seo = array();
		$tech = array();
		foreach ( $site['checks'] as $c ) {
			if ( in_array( $c['id'], $seo_ids, true ) ) {
				$seo[] = $c;
			} else {
				$tech[] = $c;
			}
		}
		$profiles = gsley_audit_collect_profiles( $socials, $site );
		$social   = gsley_audit_social_checks( $profiles );

		// Reputation: on-site review schema + user-supplied figures (labelled).
		$rep = array();
		$rep[] = gsley_audit_check( 'rep_schema', 'Review/rating schema on site', ! empty( $site['signals']['has_review_schema'] ) ? 'pass' : 'warn', ! empty( $site['signals']['has_review_schema'] ) ? 'Present' : 'Not found', 2 );
		$gr = isset( $inputs['google_rating'] ) ? (float) $inputs['google_rating'] : 0;
		$gc = isset( $inputs['google_reviews'] ) ? (int) $inputs['google_reviews'] : 0;
		if ( $gr > 0 ) {
			$rep[] = gsley_audit_check( 'google_rating', 'Google rating (you provided)', $gr >= 4.5 ? 'pass' : ( $gr >= 4.0 ? 'warn' : 'fail' ), $gr . ' / 5', 3 );
		} else {
			$rep[] = gsley_audit_check( 'google_rating', 'Google rating', 'na', 'Not provided — enter your Google Business rating for a reputation score', 3 );
		}
		if ( $gc > 0 ) {
			$rep[] = gsley_audit_check( 'google_reviews', 'Google review volume (you provided)', $gc >= 50 ? 'pass' : 'warn', $gc . ' reviews', 2 );
		} else {
			$rep[] = gsley_audit_check( 'google_reviews', 'Google review volume', 'na', 'Not provided', 2 );
		}

		$pillars[] = array( 'id' => 'website', 'label' => 'Website & tech', 'pct' => gsley_audit_score( $tech ) );
		$pillars[] = array( 'id' => 'seo', 'label' => 'SEO', 'pct' => gsley_audit_score( $seo ) );
		$pillars[] = array( 'id' => 'social', 'label' => 'Social presence', 'pct' => gsley_audit_score( $social ) );
		$pillars[] = array( 'id' => 'reputation', 'label' => 'Reputation', 'pct' => gsley_audit_score( $rep ) );
		$all_checks = array_merge( $tech, $seo, $social, $rep );
		$findings   = $all_checks;
	}

	// Overall = mean of non-zero-weight pillars that had gradable checks.
	$vals = array();
	foreach ( $pillars as $p ) {
		$vals[] = $p['pct'];
	}
	$overall = $vals ? (int) round( array_sum( $vals ) / count( $vals ) ) : 0;

	$recommendations = gsley_audit_recommendations( $all_checks );

	$summary = sprintf(
		'%d checks run on %s. Overall score %d/100. %d prioritised fixes identified.',
		count( $all_checks ),
		$website ? $website : 'your profiles',
		$overall,
		count( $recommendations )
	);

	return array(
		'overall'         => $overall,
		'pillars'         => $pillars,
		'findings'        => array_map(
			function ( $c ) {
				return array( 'label' => $c['label'], 'status' => $c['status'], 'detail' => $c['detail'] );
			},
			$findings
		),
		'summary'         => $summary,
		'recommendations' => $recommendations,
	);
}
