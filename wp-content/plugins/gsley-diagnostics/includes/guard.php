<?php
/**
 * GSLEY Diagnostics — abuse and input guards shared by every public endpoint.
 *
 * - Rate limits: per client IP, per recipient email, and a site-wide ceiling,
 *   so the public forms cannot be used to flood an inbox, burn the Resend
 *   quota, or turn the audit crawler into a traffic amplifier.
 * - Safe outbound fetch for the audit tools: public http(s) hosts on ports
 *   80/443 only, every redirect re-validated, response size capped (SSRF).
 * - Small string/IP helpers so personal data is length-capped and IPs are
 *   stored truncated.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The client IP as seen by PHP (EasyWP terminates the proxy and sets REMOTE_ADDR). */
function gsley_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/** Truncate an IP for storage: drop the last IPv4 octet / the last 80 bits of IPv6. */
function gsley_anonymize_ip( $ip ) {
	if ( function_exists( 'wp_privacy_anonymize_ip' ) ) {
		return wp_privacy_anonymize_ip( $ip );
	}
	return preg_replace( '/\.\d+$/', '.0', (string) $ip );
}

/**
 * Count a hit against a bucket and say whether it is over the limit.
 *
 * @param string $bucket Logical name (e.g. "contact-ip").
 * @param string $key    Discriminator (IP, email hash, "all").
 * @param int    $limit  Max hits per window.
 * @param int    $window Window in seconds.
 * @return bool True when the limit is exceeded (the caller should refuse).
 */
function gsley_rate_exceeded( $bucket, $key, $limit, $window ) {
	$t    = 'gsley_rl_' . substr( md5( $bucket . '|' . $key ), 0, 20 );
	$data = get_transient( $t );
	$now  = time();
	if ( ! is_array( $data ) || empty( $data['until'] ) || $data['until'] < $now ) {
		$data = array( 'n' => 0, 'until' => $now + $window );
	}
	$data['n']++;
	set_transient( $t, $data, max( 1, $data['until'] - $now ) );
	if ( $data['n'] > $limit ) {
		do_action( 'gsley_security_event', 'rate_limited', array( 'bucket' => $bucket ) );
		return true;
	}
	return false;
}

/** Limits for an action: [ per-IP, per-recipient, site-wide ] per hour. Filterable. */
function gsley_rate_limits( $action ) {
	$limits = array(
		'contact' => array( 8, 0, 120 ),
		'report'  => array( 10, 3, 300 ),
		'audit'   => array( 6, 0, 60 ),
		'payinit' => array( 10, 5, 200 ),
		'payconf' => array( 30, 0, 600 ),
	);
	$l = isset( $limits[ $action ] ) ? $limits[ $action ] : array( 10, 3, 100 );
	return apply_filters( 'gsley_rate_limits', $l, $action );
}

/**
 * Check every limit for an action. Returns '' when allowed, or a WP_Error-ready code.
 *
 * @param string $action contact|report|audit|payinit|payconf
 * @param string $email  Recipient address, when the action sends email.
 */
function gsley_guard( $action, $email = '' ) {
	list( $per_ip, $per_to, $site ) = gsley_rate_limits( $action );
	if ( $per_ip && gsley_rate_exceeded( $action . '-ip', gsley_client_ip(), $per_ip, HOUR_IN_SECONDS ) ) {
		return 'rate';
	}
	if ( $per_to && $email && gsley_rate_exceeded( $action . '-to', strtolower( $email ), $per_to, HOUR_IN_SECONDS ) ) {
		return 'rate';
	}
	if ( $site && gsley_rate_exceeded( $action . '-all', 'all', $site, HOUR_IN_SECONDS ) ) {
		return 'busy';
	}
	return '';
}

/** Plain, single-line text, capped. */
function gsley_clip( $value, $max ) {
	$value = sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
}

/** A person's name for email greetings: no links or markup, capped. */
function gsley_clean_name( $value ) {
	$name = gsley_clip( $value, 80 );
	// A name never needs a URL; dropping them stops the report email being used to carry links.
	$name = preg_replace( '#(https?://|www\.)\S+#i', '', $name );
	$name = preg_replace( '/[^\p{L}\p{M}\s\'.-]/u', '', $name );
	return trim( preg_replace( '/\s+/', ' ', $name ) );
}

/* ============================================================ SAFE FETCH (SSRF) */

/** True when an IP is public (not private, loopback, link-local, CGNAT, reserved, metadata). */
function gsley_ip_is_public( $ip ) {
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return false;
	}
	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		$long = ip2long( $ip );
		$deny = array(
			array( '0.0.0.0', 8 ),
			array( '100.64.0.0', 10 ),  // carrier-grade NAT
			array( '127.0.0.0', 8 ),
			array( '169.254.0.0', 16 ), // link-local, cloud metadata
			array( '192.0.0.0', 24 ),
			array( '198.18.0.0', 15 ),
			array( '224.0.0.0', 4 ),
		);
		foreach ( $deny as $d ) {
			$mask = -1 << ( 32 - $d[1] );
			if ( ( $long & $mask ) === ( ip2long( $d[0] ) & $mask ) ) {
				return false;
			}
		}
		return true;
	}
	$lower = strtolower( $ip );
	foreach ( array( '::', 'fc', 'fd', 'fe8', 'fe9', 'fea', 'feb', 'ff', '::ffff:' ) as $p ) {
		if ( 0 === strpos( $lower, $p ) ) {
			return false;
		}
	}
	return true;
}

/** Validate a URL for outbound fetching: http(s), default ports, a public resolvable host. */
function gsley_url_is_fetchable( $url ) {
	$p = wp_parse_url( $url );
	if ( ! $p || empty( $p['host'] ) || empty( $p['scheme'] ) || ! in_array( strtolower( $p['scheme'] ), array( 'http', 'https' ), true ) ) {
		return false;
	}
	if ( isset( $p['user'] ) || isset( $p['pass'] ) ) {
		return false;
	}
	if ( isset( $p['port'] ) && ! in_array( (int) $p['port'], array( 80, 443 ), true ) ) {
		return false;
	}
	$host = strtolower( trim( $p['host'], '[]' ) );
	if ( 'localhost' === $host || preg_match( '/\.(local|internal|localdomain|lan|home|corp)$/', $host ) ) {
		return false;
	}
	if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
		return gsley_ip_is_public( $host );
	}
	$ips = array();
	if ( function_exists( 'dns_get_record' ) ) {
		$recs = @dns_get_record( $host, DNS_A | DNS_AAAA ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		foreach ( (array) $recs as $r ) {
			if ( ! empty( $r['ip'] ) ) {
				$ips[] = $r['ip'];
			}
			if ( ! empty( $r['ipv6'] ) ) {
				$ips[] = $r['ipv6'];
			}
		}
	}
	if ( ! $ips ) {
		$ip = gethostbyname( $host );
		if ( $ip && $ip !== $host ) {
			$ips[] = $ip;
		}
	}
	if ( ! $ips ) {
		return false;
	}
	foreach ( $ips as $ip ) {
		if ( ! gsley_ip_is_public( $ip ) ) {
			return false;
		}
	}
	// Pin the connection to the address we just validated (defeats DNS rebinding).
	$GLOBALS['gsley_fetch_pin'] = array(
		'host' => $host,
		'port' => isset( $p['port'] ) ? (int) $p['port'] : ( 'https' === strtolower( $p['scheme'] ) ? 443 : 80 ),
		'ip'   => $ips[0],
	);
	return true;
}

/** cURL: resolve the pinned host to the validated IP for the duration of one audit request. */
add_action( 'http_api_curl', 'gsley_fetch_pin_curl', 10, 3 );
function gsley_fetch_pin_curl( $handle, $args, $url ) {
	if ( empty( $GLOBALS['gsley_fetch_pin'] ) || empty( $args['gsley_pinned'] ) || ! defined( 'CURLOPT_RESOLVE' ) ) {
		return;
	}
	$pin = $GLOBALS['gsley_fetch_pin'];
	if ( strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) !== $pin['host'] ) {
		return;
	}
	$ip = false !== strpos( $pin['ip'], ':' ) ? '[' . $pin['ip'] . ']' : $pin['ip'];
	curl_setopt( $handle, CURLOPT_RESOLVE, array( $pin['host'] . ':' . $pin['port'] . ':' . $ip ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Fetch a public URL for the audit engine. Follows up to 3 redirects manually,
 * re-validating each hop, with a 1 MB response cap and a short timeout.
 *
 * @return array { ok, code, body, error, url }
 */
function gsley_safe_fetch( $url ) {
	$hops = 0;
	while ( true ) {
		unset( $GLOBALS['gsley_fetch_pin'] );
		if ( ! gsley_url_is_fetchable( $url ) ) {
			return array( 'ok' => false, 'code' => 0, 'body' => '', 'error' => 'This address can’t be checked.', 'url' => $url );
		}
		$r = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 10,
				'redirection'         => 0,
				'sslverify'           => true,
				'limit_response_size' => 1048576,
				'user-agent'          => 'Mozilla/5.0 (compatible; GSLEYAudit/1.1; +https://gsleydigital.com)',
				'headers'             => array( 'Accept' => 'text/html,application/xhtml+xml' ),
				'gsley_pinned'        => true,
			)
		);
		unset( $GLOBALS['gsley_fetch_pin'] );
		if ( is_wp_error( $r ) ) {
			// Generic message: transport errors can reveal internal details.
			return array( 'ok' => false, 'code' => 0, 'body' => '', 'error' => 'Could not connect', 'url' => $url );
		}
		$code = (int) wp_remote_retrieve_response_code( $r );
		$loc  = wp_remote_retrieve_header( $r, 'location' );
		if ( $code >= 300 && $code < 400 && $loc && $hops < 3 ) {
			$hops++;
			$url = WP_Http::make_absolute_url( is_array( $loc ) ? end( $loc ) : $loc, $url );
			continue;
		}
		return array( 'ok' => true, 'code' => $code, 'body' => (string) wp_remote_retrieve_body( $r ), 'error' => '', 'url' => $url );
	}
}
