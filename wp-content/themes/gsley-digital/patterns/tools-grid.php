<?php
/**
 * Title: Free diagnostics + live audits
 * Slug: gsley-digital/tools-grid
 * Categories: gsley, columns
 * Description: The free diagnostics (flagship first), then the two live audits with their paid detail. The headline count comes from the tools registry. Layout locked; copy editable.
 * Keywords: tools, diagnostics, audits, lead magnet
 * Block Types: core/group
 * Inserter: true
 */

$gsley_free = array(
	array( 'growth-diagnostic', 'The Gsley Growth Diagnostic™ — start here', '4 min · scored', true ),
	array( 'ai-adoption-assessment-organisation', 'AI Adoption Assessment — Organisation', '6 min · board-ready', false ),
	array( 'ai-adoption-assessment-individual', 'AI Adoption Assessment — Individual', '4 min · scored', false ),
	array( 'brand-authority-audit', 'Brand Authority Audit', '5 min · scored', false ),
	array( 'revenue-leakage-calculator', 'Revenue Leakage Calculator', '3 min · figure', false ),
	array( 'marketing-maturity-scorecard', 'Marketing Maturity Scorecard', '5 min · scored', false ),
	array( 'automation-opportunity-finder', 'Automation Opportunity Finder', '4 min · mapped', false ),
	array( 'partnership-readiness-quiz', 'Partnership Readiness Quiz', '3 min · scored', false ),
);
$gsley_paid = array(
	array( 'social-media-audit', 'Social Media Audit', 'Fix list · $10' ),
	array( 'digital-presence-orm-audit', 'Digital Presence &amp; ORM Audit', 'Full plan · $20' ),
);
?>
<!-- wp:group {"tagName":"section","className":"section section--bone","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section section--bone">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:group {"className":"section-head reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group section-head reveal">
			<!-- wp:heading {"className":"h2"} -->
			<h2 class="wp-block-heading h2">[gsley_count type="all-tools" cap="true"] free diagnostics. Each ends in a scored read-out and a recommendation.</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"lede"} -->
			<p class="lede">Free, instant, no call required. The fastest way to see where your commercial system is leaking.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
		<div class="tools-grid reveal">
<?php foreach ( $gsley_free as $t ) : ?>
			<a href="/tools/<?php echo esc_attr( $t[0] ); ?>/" class="tool-row<?php echo $t[3] ? ' tool-row--flag' : ''; ?>"><span class="tool-row__name"><?php echo wp_kses_post( $t[1] ); ?></span><span class="tool-row__meta"><?php echo esc_html( $t[2] ); ?></span></a>
<?php endforeach; ?>
		</div>
		<!-- /wp:html -->

		<!-- wp:paragraph {"className":"tools-note reveal"} -->
		<p class="tools-note reveal">Two run live checks on your channels. The score is free; the detailed fix list or remediation plan is paid.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<div class="tools-grid reveal">
<?php foreach ( $gsley_paid as $t ) : ?>
			<a href="/tools/<?php echo esc_attr( $t[0] ); ?>/" class="tool-row"><span class="tool-row__name"><?php echo wp_kses_post( $t[1] ); ?></span><span class="tool-row__meta"><?php echo esc_html( $t[2] ); ?></span></a>
<?php endforeach; ?>
		</div>
		<p class="u-mt-8"><a href="/tools/" class="open-link">See all tools ↗</a></p>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
