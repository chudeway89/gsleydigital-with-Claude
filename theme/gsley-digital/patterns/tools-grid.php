<?php
/**
 * Title: Tools Gallery
 * Slug: gsley-digital/tools-grid
 * Categories: gsley, columns, call-to-action
 * Description: Gallery of the eight free diagnostics as linked chips. Layout and tool links locked; section heading and lede editable.
 * Keywords: tools, diagnostics, gallery, chips
 * Block Types: core/group
 * Inserter: true
 */

$gsley_tools = array(
	array( 'The Gsley Growth Diagnostic™', '4 min · scored', '/tools/growth-diagnostic' ),
	array( 'AI Adoption Assessment — Organisation', '6 min · board-ready', '/tools/ai-adoption-assessment-organisation' ),
	array( 'AI Adoption Assessment — Individual', '4 min · scored', '/tools/ai-adoption-assessment-individual' ),
	array( 'Brand Authority Audit', '5 min · scored', '/tools/brand-authority-audit' ),
	array( 'Revenue Leakage Calculator', '3 min · figure', '/tools/revenue-leakage-calculator' ),
	array( 'Marketing Maturity Scorecard', '5 min · scored', '/tools/marketing-maturity-scorecard' ),
	array( 'Automation Opportunity Finder', '4 min · mapped', '/tools/automation-opportunity-finder' ),
	array( 'Partnership Readiness Quiz', '3 min · scored', '/tools/partnership-readiness-quiz' ),
	array( 'Social Media Audit', '$10 · audit', '/tools/social-media-audit' ),
	array( 'Digital Presence &amp; ORM Audit', '$20 · audit', '/tools/digital-presence-orm-audit' ),
);
?>
<!-- wp:group {"tagName":"section","className":"section section--bone","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section section--bone">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 05</span><span class="marker__rule"></span><span>Free diagnostics</span></div>
		<!-- /wp:html -->

		<!-- wp:group {"className":"start-head reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group start-head reveal">
			<!-- wp:paragraph {"className":"h2"} -->
			<p class="h2">Ten tools. Each ends in a scored read-out and a clear next step.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"lede u-mt-4"} -->
			<p class="lede u-mt-4">Free, instant, no call required. The fastest way to see where your commercial system is leaking.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
		<div class="tools-grid">
			<?php foreach ( $gsley_tools as $tool ) :
				list( $name, $meta, $url ) = $tool; ?>
			<a href="<?php echo $url; ?>" class="tool-chip"><span class="tool-chip__name"><?php echo $name; ?></span><span class="tool-chip__meta"><?php echo $meta; ?></span></a>
			<?php endforeach; ?>
		</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
