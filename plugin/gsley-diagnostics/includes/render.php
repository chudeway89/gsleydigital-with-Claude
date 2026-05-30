<?php
/**
 * GSLEY Diagnostics — block render callback.
 *
 * Emits a small container plus the tool config as JSON; the shared engine
 * (assets/tool.js) builds the answer-first UI inside it on the front end.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	$cfg = $configs[ $tool ];

	// Ensure the front-end assets load only when a tool is on the page.
	wp_enqueue_style( 'gsley-diagnostics' );
	wp_enqueue_script( 'gsley-diagnostics' );

	$json = wp_json_encode( $cfg );

	$wrapper = function_exists( 'get_block_wrapper_attributes' )
		? get_block_wrapper_attributes( array( 'class' => 'gsley-tool', 'data-engine' => $cfg['engine'] ) )
		: 'class="gsley-tool" data-engine="' . esc_attr( $cfg['engine'] ) . '"';

	$noscript = sprintf(
		/* translators: %s: tool title */
		esc_html__( 'The %s needs JavaScript enabled. Please enable it, or contact us and we’ll run it with you.', 'gsley-diagnostics' ),
		esc_html( $cfg['title'] )
	);

	return sprintf(
		'<div %1$s><script type="application/json" class="gsley-tool-config">%2$s</script><noscript><p>%3$s <a href="/contact">%4$s</a></p></noscript></div>',
		$wrapper,
		$json, // JSON in a application/json script tag; not HTML-executed.
		$noscript,
		esc_html__( 'Contact GSLEY', 'gsley-diagnostics' )
	);
}
