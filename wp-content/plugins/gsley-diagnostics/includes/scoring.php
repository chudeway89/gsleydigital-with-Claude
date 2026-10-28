<?php
/**
 * GSLEY Diagnostics — server-side scoring.
 *
 * The browser shows the result instantly, but the report that is stored and
 * emailed is recomputed here from the raw answers / inputs / selections. Nothing
 * the browser sends as a score, band, label or priority is trusted, so the
 * report email can't be made to carry arbitrary text or links.
 * Mirrors the engines in assets/tool.js.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate a submission body and rebuild its result from first principles.
 *
 * @param array $cfg  Tool config.
 * @param array $body Decoded JSON body.
 * @return array|WP_Error Clean result array.
 */
function gsley_score_submission( $cfg, $body ) {
	switch ( $cfg['engine'] ) {
		case 'likert':
			return gsley_score_likert( $cfg, isset( $body['answers'] ) ? $body['answers'] : null );
		case 'calculator':
			return gsley_score_calculator( $cfg, isset( $body['inputs'] ) ? $body['inputs'] : null );
		case 'finder':
			return gsley_score_finder( $cfg, isset( $body['selections'] ) ? $body['selections'] : null );
	}
	return new WP_Error( 'gsley_engine', 'This tool does not email reports.', array( 'status' => 400 ) );
}

function gsley_score_likert( $cfg, $answers ) {
	$n = count( $cfg['questions'] );
	if ( ! is_array( $answers ) || count( $answers ) !== $n ) {
		return new WP_Error( 'gsley_answers', 'Please answer every statement.', array( 'status' => 422 ) );
	}
	$max   = count( $cfg['scale'] ) - 1;
	$clean = array();
	foreach ( array_values( $answers ) as $a ) {
		if ( ! is_numeric( $a ) || (int) $a < 0 || (int) $a > $max ) {
			return new WP_Error( 'gsley_answers', 'Please answer every statement.', array( 'status' => 422 ) );
		}
		$clean[] = (int) $a;
	}
	$by = array();
	foreach ( $cfg['dimensions'] as $d ) {
		$by[ $d['id'] ] = array( 'sum' => 0, 'count' => 0 );
	}
	foreach ( $cfg['questions'] as $i => $q ) {
		$by[ $q['dim'] ]['sum'] += $clean[ $i ];
		$by[ $q['dim'] ]['count']++;
	}
	$dims = array();
	foreach ( $cfg['dimensions'] as $d ) {
		$o      = $by[ $d['id'] ];
		$dims[] = array( 'id' => $d['id'], 'pct' => $o['count'] ? (int) round( $o['sum'] / ( $o['count'] * $max ) * 100 ) : 0 );
	}
	$overall = (int) round( array_sum( wp_list_pluck( $dims, 'pct' ) ) / max( 1, count( $dims ) ) );
	$band    = '';
	foreach ( $cfg['bands'] as $b ) {
		if ( $overall >= $b['min'] && $overall <= $b['max'] ) {
			$band = $b['name'];
			break;
		}
	}
	$lowest = $dims[0];
	foreach ( $dims as $d ) {
		if ( $d['pct'] < $lowest['pct'] ) {
			$lowest = $d;
		}
	}
	return array( 'answers' => $clean, 'overall' => $overall, 'band' => $band, 'dimensions' => $dims, 'priority' => $lowest['id'] );
}

function gsley_score_calculator( $cfg, $inputs ) {
	if ( ! is_array( $inputs ) ) {
		return new WP_Error( 'gsley_inputs', 'Please enter your numbers.', array( 'status' => 422 ) );
	}
	$v = array();
	foreach ( $cfg['inputs'] as $in ) {
		$raw              = isset( $inputs[ $in['id'] ] ) && is_numeric( $inputs[ $in['id'] ] ) ? (float) $inputs[ $in['id'] ] : 0.0;
		$v[ $in['id'] ] = max( 0.0, min( 1e13, $raw ) ); // non-negative, bounded
	}
	$b     = $cfg['benchmarks'];
	$rev   = isset( $v['revenue'] ) ? $v['revenue'] : 0;
	$pct   = function ( $x ) {
		return max( 0, min( 100, $x ) );
	};
	$comps = array(
		array( 'id' => 'conversion', 'amount' => $rev * max( 0, $b['benchmarkConv'] - ( isset( $v['conv'] ) ? $v['conv'] : 0 ) ) / 100 ),
		array( 'id' => 'retention', 'amount' => $rev * $pct( ( ( isset( $v['churn'] ) ? $v['churn'] : 0 ) - $b['targetChurn'] ) * 12 ) / 100 ),
		array( 'id' => 'pricing', 'amount' => $rev * max( 0, ( isset( $v['discount'] ) ? $v['discount'] : 0 ) - $b['floorDiscount'] ) / 100 ),
		array( 'id' => 'expansion', 'amount' => $rev * max( 0, $b['targetExpansion'] - ( isset( $v['expansion'] ) ? $v['expansion'] : 0 ) ) / 100 ),
	);
	$total = array_sum( wp_list_pluck( $comps, 'amount' ) );
	if ( $rev > 0 ) {
		$total = min( $total, $rev );
	}
	$dominant = $comps[0];
	foreach ( $comps as $c ) {
		if ( $c['amount'] > $dominant['amount'] ) {
			$dominant = $c;
		}
	}
	foreach ( $comps as &$c ) {
		$c['amount'] = (int) round( $c['amount'] );
	}
	unset( $c );
	return array( 'inputs' => $v, 'total' => (int) round( $total ), 'components' => $comps, 'priority' => $dominant['id'] );
}

function gsley_score_finder( $cfg, $selections ) {
	if ( ! is_array( $selections ) || ! $selections ) {
		return new WP_Error( 'gsley_selections', 'Please choose at least one area.', array( 'status' => 422 ) );
	}
	$areas = array();
	foreach ( $cfg['areas'] as $a ) {
		$areas[ $a['id'] ] = $a;
	}
	$clean = array();
	foreach ( array_slice( $selections, 0, count( $areas ) ) as $s ) {
		if ( ! is_array( $s ) || empty( $s['area'] ) || ! isset( $areas[ $s['area'] ] ) || isset( $clean[ $s['area'] ] ) ) {
			continue;
		}
		$f = isset( $s['freq'] ) ? max( 1, min( 3, (int) $s['freq'] ) ) : 2;
		$p = isset( $s['pain'] ) ? max( 1, min( 3, (int) $s['pain'] ) ) : 2;
		$clean[ $s['area'] ] = array( 'area' => $s['area'], 'freq' => $f, 'pain' => $p, 'score' => $f * $p );
	}
	if ( ! $clean ) {
		return new WP_Error( 'gsley_selections', 'Please choose at least one area.', array( 'status' => 422 ) );
	}
	$ranked = array_values( $clean );
	usort(
		$ranked,
		function ( $a, $b ) {
			return $b['score'] <=> $a['score'];
		}
	);
	$top = array_slice( wp_list_pluck( $ranked, 'area' ), 0, 5 );
	return array( 'selections' => $ranked, 'top' => $top, 'priority' => $areas[ $top[0] ]['service'] );
}
