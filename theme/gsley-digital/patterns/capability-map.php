<?php
/**
 * Title: Capability Map
 * Slug: gsley-digital/capability-map
 * Categories: gsley, columns
 * Description: Six differentiator cards mapped to their pillar, with strength bars. Layout, ids, glyphs and bars locked; headings and copy editable.
 * Keywords: capabilities, differentiators, grid, cards
 * Block Types: core/group
 * Inserter: true
 */

$gsley_caps = array(
	array( 'C-01', 'Market pillar', 'm', 5, 'Demand Generation Systems', 'Compounding content engines, paid media architecture, and lifecycle automation engineered for predictable B2B and institutional pipeline.' ),
	array( 'C-02', 'Market pillar', 'm', 4, 'B2B Sales Enablement &amp; Commercialisation', 'CRM architecture, named-account playbooks, and pricing logic — the commercial spine that turns marketing momentum into booked revenue.' ),
	array( 'C-03', 'Market pillar', 'm', 6, 'Strategic Partnerships', 'Multi-stakeholder dealmaking between institutions, corporates, and foundations. Productised as Sprints and Deal Desks.' ),
	array( 'C-04', 'Market pillar', 'm', 4, 'Institutional Brand &amp; Reputation', 'Institutional-grade brand systems, executive storytelling, and online reputation management for organisations where trust is the asset.' ),
	array( 'C-05', 'Digital pillar', 'd', 5, 'AI Agent Deployment', 'Agentic workflows wired into the back office. Prebuilt patterns for hospitals, foundations, and global enterprise — not toy demos.' ),
	array( 'C-06', 'Digital pillar', 'd', 6, 'Data Infrastructure &amp; Automation', 'Unified data, governed access, and automation that compounds across RevOps, finance, and operations. Measurable, auditable, owned.' ),
);
?>
<!-- wp:group {"tagName":"section","className":"section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 03</span><span class="marker__rule"></span><span>Capability map</span></div>
		<!-- /wp:html -->

		<!-- wp:group {"className":"cap-head reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group cap-head reveal">
			<!-- wp:paragraph {"className":"h2"} -->
			<p class="h2">Six differentiators —<br>mapped to the pillar they live inside.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"lede"} -->
			<p class="lede">We work where evidence is strongest. Each capability below combines documented mastery, repeatable methodology, and AI-augmented delivery — the basis for premium positioning.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"cap-grid reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group cap-grid reveal">
			<?php foreach ( $gsley_caps as $cap ) :
				list( $code, $pillar, $fill, $filled, $title, $desc ) = $cap;
				$glyph        = ( 'd' === $fill ) ? '◇' : '▲';
				$glyph_class  = ( 'd' === $fill ) ? 'glyph--digital' : 'glyph--market';
				$pillar_style = ( 'd' === $fill ) ? ' style="color:var(--accent-digital)"' : '';
				$bars         = '';
				for ( $i = 0; $i < 7; $i++ ) {
					$bars .= ( $i < $filled ) ? '<span class="fill-' . $fill . '"></span>' : '<span></span>';
				}
				?>
			<!-- wp:group {"className":"cap-card","layout":{"type":"default"}} -->
			<div class="wp-block-group cap-card">
				<!-- wp:html -->
				<div class="cap-card__top"><div class="cap-card__id"><span class="code"><?php echo $code; ?></span><span class="pillar"<?php echo $pillar_style; ?>><?php echo $pillar; ?></span></div><span class="glyph <?php echo $glyph_class; ?>"><?php echo $glyph; ?></span></div>
				<!-- /wp:html -->
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php echo $title; ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p><?php echo $desc; ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:html -->
				<div class="cap-card__bars"><?php echo $bars; ?></div>
				<!-- /wp:html -->
			</div>
			<!-- /wp:group -->
			<?php endforeach; ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
