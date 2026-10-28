<?php
/**
 * GSLEY Diagnostics — report email builder.
 *
 * Renders the shared report skeleton (diagnostics-tool-spec.md §3), tokenised
 * per tool and per engine. Editorial voice; no invented statistics.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gsley_money( $n ) {
	return '&#8358;' . number_format( (float) $n );
}

/**
 * @param string $tool       Tool slug.
 * @param array  $submission Sanitised submission (email, name, result).
 * @return array { subject:string, html:string }
 */
function gsley_build_report_email( $tool, $submission ) {
	$configs = gsley_diagnostics_configs();
	$cfg     = isset( $configs[ $tool ] ) ? $configs[ $tool ] : null;
	$r       = isset( $submission['result'] ) ? $submission['result'] : array();
	$name    = ! empty( $submission['name'] ) ? $submission['name'] : '';
	$title   = $cfg ? $cfg['title'] : 'GSLEY Diagnostic';

	$engine = $cfg ? $cfg['engine'] : 'likert';
	$subject = 'Your ' . $title . ' result';
	$headline = '';
	$means = '';
	$breakdown = '';
	$priority_block = '';

	if ( 'likert' === $engine ) {
		$overall = isset( $r['overall'] ) ? (int) $r['overall'] : 0;
		$band    = isset( $r['band'] ) ? $r['band'] : '';
		$subject = sprintf( 'Your %s: %s (%d/100)', $title, $band, $overall );
		$headline = sprintf( '<strong style="font-size:32px;color:#0F1B2D">%d<span style="font-size:16px;color:#4A5568">/100</span></strong> &nbsp; <span style="font-family:Georgia,serif;font-size:22px">%s</span>', $overall, esc_html( $band ) );

		$band_obj = null;
		if ( $cfg ) {
			foreach ( $cfg['bands'] as $b ) {
				if ( $overall >= $b['min'] && $overall <= $b['max'] ) { $band_obj = $b; break; }
			}
		}
		$means = $band_obj ? esc_html( $band_obj['blurb'] ) : '';

		$labels = array();
		if ( $cfg ) {
			foreach ( $cfg['dimensions'] as $d ) { $labels[ $d['id'] ] = $d['label']; }
		}
		$rows = '';
		if ( ! empty( $r['dimensions'] ) && is_array( $r['dimensions'] ) ) {
			foreach ( $r['dimensions'] as $d ) {
				$lab = isset( $labels[ $d['id'] ] ) ? $labels[ $d['id'] ] : $d['id'];
				$pct = (int) $d['pct'];
				$is_low = ( isset( $r['priority'] ) && $r['priority'] === $d['id'] );
				$rows .= gsley_report_bar( $lab . ( $is_low ? ' &middot; priority' : '' ), $pct . '%', $pct, $is_low ? '#B7791F' : '#16B8A6' );
			}
		}
		$breakdown = $rows;

		if ( $cfg && isset( $r['priority'] ) && isset( $cfg['recommendations'][ $r['priority'] ] ) ) {
			$priority_block = gsley_report_priority( $cfg['recommendations'][ $r['priority'] ] );
		}
	} elseif ( 'calculator' === $engine ) {
		$total = isset( $r['total'] ) ? (float) $r['total'] : 0;
		$subject = sprintf( 'Your %s: %s/yr (estimate)', $title, html_entity_decode( gsley_money( $total ) ) );
		$headline = '<strong style="font-family:Georgia,serif;font-size:34px;color:#0F1B2D">&#8776; ' . gsley_money( $total ) . '</strong><br><span style="font-size:13px;color:#4A5568">estimated recoverable each year &mdash; an estimate from your inputs, not a measurement</span>';
		$means = isset( $cfg['figureCaption'] ) ? esc_html( $cfg['figureCaption'] ) : '';

		$labels = array( 'conversion' => 'Conversion leak', 'retention' => 'Retention leak', 'pricing' => 'Pricing leak', 'expansion' => 'Expansion gap' );
		$max = 1;
		if ( ! empty( $r['components'] ) ) {
			foreach ( $r['components'] as $c ) { $max = max( $max, (float) $c['amount'] ); }
		}
		$rows = '';
		if ( ! empty( $r['components'] ) ) {
			foreach ( $r['components'] as $c ) {
				$lab = isset( $labels[ $c['id'] ] ) ? $labels[ $c['id'] ] : $c['id'];
				$rows .= gsley_report_bar( $lab, html_entity_decode( gsley_money( $c['amount'] ) ), (int) round( $c['amount'] / $max * 100 ), '#16B8A6' );
			}
		}
		$breakdown = $rows;
		if ( $cfg && isset( $r['priority'] ) ) {
			$rec = isset( $cfg['recommendations'][ $r['priority'] ] ) ? $cfg['recommendations'][ $r['priority'] ] : $cfg['recommendations']['default'];
			$priority_block = gsley_report_priority( $rec );
		}
	} else { // finder
		$subject = 'Your top automation opportunities';
		$headline = '<strong style="font-family:Georgia,serif;font-size:24px;color:#0F1B2D">Your highest-return opportunities</strong>';
		$means = 'Ranked by how often each workflow happens and how painful it is today. Start at the top.';
		$areas = array();
		if ( $cfg ) {
			foreach ( $cfg['areas'] as $a ) { $areas[ $a['id'] ] = $a; }
		}
		$rows = '';
		if ( ! empty( $r['top'] ) && is_array( $r['top'] ) ) {
			$i = 1;
			foreach ( $r['top'] as $aid ) {
				if ( ! isset( $areas[ $aid ] ) ) { continue; }
				$a = $areas[ $aid ];
				$rows .= '<tr><td style="padding:10px 0;border-bottom:1px solid #E5E2D8;vertical-align:top">'
					. '<span style="font-family:monospace;color:#16B8A6">#' . $i . '</span></td>'
					. '<td style="padding:10px 0 10px 12px;border-bottom:1px solid #E5E2D8">'
					. '<strong style="color:#0F1B2D">' . esc_html( $a['label'] ) . '</strong><br>'
					. '<span style="font-size:13px;color:#4A5568">' . esc_html( $a['method'] ) . '</span><br>'
					. '<span style="font-family:monospace;font-size:11px;color:#4A5568">' . esc_html( $a['effort'] ) . ' effort</span></td></tr>';
				$i++;
			}
		}
		$breakdown = '<table style="width:100%;border-collapse:collapse">' . $rows . '</table>';
		if ( $cfg && isset( $cfg['recommendations']['default'] ) ) {
			$priority_block = gsley_report_priority( $cfg['recommendations']['default'] );
		}
	}

	$greeting = $name ? ( 'Hi ' . esc_html( $name ) . ',' ) : 'Hi,';
	$plan = gsley_report_plan( $cfg, $r );

	$html = '<div style="max-width:600px;margin:0 auto;font-family:Helvetica,Arial,sans-serif;color:#1F2C3F;line-height:1.55">'
		. '<div style="background:#0F1B2D;color:#FAF9F5;padding:24px 28px;border-radius:10px 10px 0 0">'
		. '<div style="font-family:monospace;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#16B8A6">GSLEY · ' . esc_html( $title ) . '</div>'
		. '<div style="margin-top:10px">' . $headline . '</div></div>'
		. '<div style="border:1px solid #E5E2D8;border-top:none;border-radius:0 0 10px 10px;padding:24px 28px;background:#FAF9F5">'
		. '<p>' . $greeting . '</p>'
		. ( $means ? '<p><strong>What this means.</strong> ' . $means . '</p>' : '' )
		. ( $breakdown ? '<h3 style="font-family:Georgia,serif;font-weight:normal;color:#0F1B2D;margin-top:24px">Your breakdown</h3>' . $breakdown : '' )
		. $priority_block
		. $plan
		. '<div style="margin-top:28px;padding-top:20px;border-top:1px solid #E5E2D8">'
		. '<a href="https://gsleydigital.com/contact/" style="display:inline-block;background:#16B8A6;color:#0F1B2D;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:600">Book a discovery call &#8599;</a>'
		. ' &nbsp; <a href="https://wa.me/2349024767079" style="color:#0F1B2D">or WhatsApp us</a></div>'
		. '<p style="font-size:12px;color:#8895A7;margin-top:24px">You received this because you requested it from a GSLEY diagnostic. '
		. 'Unsubscribe anytime &mdash; see our <a href="https://gsleydigital.com/legal/privacy/" style="color:#8895A7">privacy policy</a>.</p>'
		. '</div></div>';

	return array( 'subject' => $subject, 'html' => $html );
}

function gsley_report_bar( $label, $value, $pct, $color ) {
	$pct = max( 0, min( 100, (int) $pct ) );
	return '<div style="margin:10px 0">'
		. '<div style="display:flex;justify-content:space-between;font-size:14px"><span>' . wp_kses_post( $label ) . '</span>'
		. '<span style="font-family:monospace;color:#4A5568">' . wp_kses_post( $value ) . '</span></div>'
		. '<div style="height:8px;background:#F1EFE7;border-radius:999px;overflow:hidden;margin-top:4px">'
		. '<div style="height:8px;width:' . $pct . '%;background:' . esc_attr( $color ) . ';border-radius:999px"></div></div></div>';
}

function gsley_report_priority( $rec ) {
	return '<div style="background:#0F1B2D;color:#FAF9F5;border-radius:10px;padding:20px 24px;margin:24px 0">'
		. '<div style="font-family:monospace;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#16B8A6">Your priority</div>'
		. '<h3 style="font-family:Georgia,serif;font-weight:normal;margin:8px 0;color:#FAF9F5">' . esc_html( $rec['title'] ) . '</h3>'
		. '<p style="color:rgba(250,249,245,.85)">' . esc_html( $rec['body'] ) . '</p>'
		. '<a href="https://gsleydigital.com' . esc_url( $rec['url'] ) . '" style="color:#16B8A6;font-weight:600">' . esc_html( $rec['linkText'] ) . ' &#8599;</a></div>';
}

/**
 * A simple, real, sequenced 90-day plan derived from the priority — no fake metrics.
 */
function gsley_report_plan( $cfg, $r ) {
	$link = 'an executable sprint';
	if ( $cfg && isset( $r['priority'] ) && isset( $cfg['recommendations'][ $r['priority'] ] ) ) {
		$link = $cfg['recommendations'][ $r['priority'] ]['linkText'];
	} elseif ( $cfg && isset( $cfg['recommendations']['default'] ) ) {
		$link = $cfg['recommendations']['default']['linkText'];
	}
	$steps = array(
		array( 'Days 0&ndash;30 &middot; Diagnose', 'Scope the priority above against your context and data, and agree the baseline you&#8217;ll move.' ),
		array( 'Days 30&ndash;60 &middot; Architect', 'Map the named archetype to your constraints &mdash; you see the full method before work begins.' ),
		array( 'Days 60&ndash;90 &middot; Deploy', 'Ship the first increment under explicit governance, then transfer the operating manual to your team.' ),
	);
	$rows = '';
	foreach ( $steps as $s ) {
		$rows .= '<div style="margin:12px 0"><div style="font-family:monospace;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#16B8A6">' . $s[0] . '</div>'
			. '<div style="font-size:14px">' . $s[1] . '</div></div>';
	}
	return '<h3 style="font-family:Georgia,serif;font-weight:normal;color:#0F1B2D;margin-top:24px">A sequenced 90-day plan</h3>' . $rows;
}
