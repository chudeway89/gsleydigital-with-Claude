<?php
/**
 * GSLEY Diagnostics — block render callback.
 *
 * The served HTML carries the tool's real content — title, one-paragraph
 * description, time, output, and FAQ — so search engines, link previews, and
 * visitors without JavaScript see a complete page. The shared engine
 * (assets/tool.js) then mounts the interactive part in place of the intro.
 * Each tool also prints WebApplication + FAQPage JSON-LD.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Is the current request a /tools/{slug} page (so the tool owns the page H1)? */
function gsley_diagnostics_is_tool_page() {
	if ( ! is_page() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post || empty( $post->post_parent ) ) {
		return false;
	}
	$parent = get_post( $post->post_parent );
	return $parent && 'tools' === $parent->post_name;
}

/**
 * FAQ pairs for a tool, written from its configuration so every answer is true
 * of how the tool actually works.
 *
 * @return array<int,array{0:string,1:string}>
 */
function gsley_diagnostics_tool_faq( $cfg, $meta ) {
	// "the Brand Authority Audit", but not "the The Gsley Growth Diagnostic™".
	$title  = 0 === strpos( $cfg['title'], 'The ' ) ? $cfg['title'] : 'the ' . $cfg['title'];
	$engine = $cfg['engine'];
	// "4 min" → "four minutes".
	$time = preg_match( '/^(\d+)\s*min/', $meta['time'], $tm ) ? gsley_number_word( (int) $tm[1] ) . ' minutes' : $meta['time'];
	$faq    = array();

	switch ( $engine ) {
		case 'calculator':
			$n     = isset( $cfg['inputs'] ) ? count( $cfg['inputs'] ) : 0;
			$faq[] = array( 'How long does ' . $title . ' take?', 'About ' . $time . '. You enter ' . gsley_number_word( $n ) . ' numbers about your commercial engine, and the estimate updates from those numbers alone.' );
			$faq[] = array( 'Is the figure a benchmark?', 'No. It is an estimate calculated only from the numbers you enter, not a comparison with other organisations. Treat it as a way to size the problem, not as a forecast.' );
			$what  = 'estimate and its breakdown';
			break;
		case 'finder':
			$faq[] = array( 'How long does ' . $title . ' take?', 'About ' . $time . '. You choose the workflow areas that apply to your team and rate each one for how often it happens and how painful it is. The opportunities are then ranked.' );
			$what  = 'ranked opportunities';
			break;
		case 'audit':
			$faq[] = array( 'What does ' . $title . ' check?', 'You enter your ' . ( 'orm' === ( isset( $cfg['subtype'] ) ? $cfg['subtype'] : '' ) ? 'website and, optionally, your social profiles and Google rating' : 'social profile links and, optionally, your website' ) . '. The audit then runs live checks on those properties while you wait and scores what it finds.' );
			$faq[] = array( 'What is free, and what is paid?', 'The score is free. The detailed ' . ( 'orm' === ( isset( $cfg['subtype'] ) ? $cfg['subtype'] : '' ) ? 'remediation plan' : 'fix list' ) . ' costs ' . ( isset( $cfg['priceLabel'] ) ? $cfg['priceLabel'] : '' ) . ', paid by card through Paystack, and is sent to the email address you give at checkout.' );
			$what  = 'score';
			break;
		default:
			$n     = isset( $cfg['questions'] ) ? count( $cfg['questions'] ) : 0;
			$faq[] = array( 'How long does ' . $title . ' take?', 'About ' . $time . '. There are ' . gsley_number_word( $n ) . ' statements, each answered on a five-point scale from strongly disagree to strongly agree.' );
			$what  = 'score';
	}

	if ( 'audit' !== $engine ) {
		$faq[] = array( 'Do I need to give my email to see the result?', 'No. Your ' . $what . ' and the main recommendation appear on screen first. Email is optional, and only needed if you want the full report sent to you.' );
	}
	if ( ! empty( $cfg['reportLines'] ) ) {
		$faq[] = array( 'What is in the full report?', implode( '; ', $cfg['reportLines'] ) . '.' );
	}
	$faq[] = array( 'What happens to my answers?', 'If you ask for the report, your answers and result are stored with your email address so we can send it. We do not sell your data. Our privacy policy has the detail.' );
	return $faq;
}

/**
 * Render a tool block.
 *
 * @param array  $attributes Block attributes (expects 'tool').
 * @param string $content    Inner content (unused).
 * @param object $block      Block instance.
 * @return string
 */
function gsley_diagnostics_render( $attributes, $content, $block ) {
	$configs = gsley_diagnostics_configs();

	// Resolve the tool slug from the attribute or the block name.
	$tool = isset( $attributes['tool'] ) ? sanitize_key( $attributes['tool'] ) : '';
	if ( ! $tool && isset( $block->name ) ) {
		$tool = str_replace( 'gsley/tool-', '', $block->name );
	}
	if ( ! isset( $configs[ $tool ] ) ) {
		return '';
	}

	$cfg   = $configs[ $tool ];
	$tools = gsley_tools();
	$meta  = isset( $tools[ $tool ] ) ? $tools[ $tool ] : array( 'time' => '', 'out' => '', 'paid' => false, 'url' => '' );

	// The small label above the title: time and output (no catalogue codes).
	$cfg['code'] = trim( $meta['time'] . ' · ' . $meta['out'], ' ·' );

	// Ensure the front-end assets load only when a tool is on the page.
	wp_enqueue_style( 'gsley-diagnostics' );
	wp_enqueue_script( 'gsley-diagnostics' );

	$on_tool_page = gsley_diagnostics_is_tool_page();
	$h            = $on_tool_page ? 'h1' : 'h2';
	$faq          = gsley_diagnostics_tool_faq( $cfg, $meta );

	$wrapper = function_exists( 'get_block_wrapper_attributes' )
		? get_block_wrapper_attributes( array( 'class' => 'gsley-tool', 'data-engine' => $cfg['engine'] ) )
		: 'class="gsley-tool" data-engine="' . esc_attr( $cfg['engine'] ) . '"';

	// Server-rendered intro (replaced by the interactive intro once tool.js runs).
	$facts = '';
	foreach ( (array) ( isset( $cfg['facts'] ) ? $cfg['facts'] : array() ) as $f ) {
		$facts .= '<div><span class="v">' . esc_html( $f['v'] ) . '</span><span class="k">' . esc_html( $f['k'] ) . '</span></div>';
	}
	$intro = '<section class="gt-screen gt-intro is-active gt-ssr" aria-label="Introduction">'
		. ( $on_tool_page ? '<a class="gt-back-link" href="' . esc_url( home_url( '/tools/' ) ) . '">← All tools</a><br>' : '' )
		. '<span class="gt-mono">' . esc_html( $cfg['code'] ) . '</span>'
		. '<' . $h . '>' . esc_html( $cfg['title'] ) . '</' . $h . '>'
		. '<p class="gt-lede">' . esc_html( $cfg['lede'] ) . '</p>'
		. ( $facts ? '<div class="gt-facts">' . $facts . '</div>' : '' )
		. '<noscript><p class="gt-fineprint">' . esc_html__( 'This tool needs JavaScript to run. Enable it, or contact us and we’ll run it with you:', 'gsley-diagnostics' ) . ' <a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact GSLEY', 'gsley-diagnostics' ) . '</a></p></noscript>'
		. '</section>';

	// FAQ (outside the tool root, so it stays when the tool mounts).
	$faq_html = '<section class="gt-faq" aria-labelledby="gt-faq-' . esc_attr( $tool ) . '"><h2 class="h2" id="gt-faq-' . esc_attr( $tool ) . '">Questions</h2><div class="faq">';
	foreach ( $faq as $i => $qa ) {
		$faq_html .= '<details class="faq__item"' . ( 0 === $i ? ' open' : '' ) . '><summary class="faq__q">' . esc_html( $qa[0] ) . '<span class="pm" aria-hidden="true">+</span></summary><div class="faq__a">' . esc_html( $qa[1] ) . '</div></details>';
	}
	$faq_html .= '</div></section>';

	// Schema: WebApplication + FAQPage.
	$url    = ! empty( $meta['url'] ) ? $meta['url'] : home_url( '/tools/' . $tool . '/' );
	$offers = array( array( '@type' => 'Offer', 'price' => '0', 'priceCurrency' => isset( $cfg['currency'] ) ? $cfg['currency'] : 'USD', 'name' => 'audit' === $cfg['engine'] ? 'Free score' : 'Free' ) );
	if ( ! empty( $cfg['price'] ) ) {
		$offers[] = array( '@type' => 'Offer', 'price' => (string) $cfg['price'], 'priceCurrency' => isset( $cfg['currency'] ) ? $cfg['currency'] : 'USD', 'name' => 'Detailed report' );
	}
	$graph = array(
		array(
			'@type'               => 'WebApplication',
			'@id'                 => $url . '#app',
			'name'                => $cfg['title'],
			'url'                 => $url,
			'description'         => $cfg['lede'],
			'applicationCategory' => 'BusinessApplication',
			'operatingSystem'     => 'Any (web browser)',
			'isAccessibleForFree' => true,
			'offers'              => $offers,
			'provider'            => array( '@id' => home_url( '/#organization' ) ),
		),
	);
	if ( $on_tool_page ) {
		$q = array();
		foreach ( $faq as $qa ) {
			$q[] = array( '@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa[1] ) );
		}
		$graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $q );
	}
	$schema = '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>';

	return '<div class="gsley-tool-wrap">'
		. sprintf(
			'<div %1$s>%2$s<script type="application/json" class="gsley-tool-config">%3$s</script></div>',
			$wrapper,
			$intro,
			wp_json_encode( $cfg ) // JSON in an application/json script tag; not HTML-executed.
		)
		. $faq_html . $schema . '</div>';
}
