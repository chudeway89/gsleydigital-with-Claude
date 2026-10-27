<?php
/**
 * Title: Architecture — Two Pillars
 * Slug: gsley-digital/architecture
 * Categories: gsley, columns, featured
 * Description: Two-cell grid introducing the Market (▲) and Digital (◇) pillars. Layout and pillar glyphs locked; headings, copy and tags editable.
 * Keywords: pillars, market, digital, architecture, grid
 * Block Types: core/group
 * Inserter: true
 */
?>
<!-- wp:group {"tagName":"section","className":"section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 01</span><span class="marker__rule"></span><span>Architecture</span></div>
		<!-- /wp:html -->

		<!-- wp:group {"className":"arch-grid reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group arch-grid reveal">
			<!-- wp:group {"className":"arch-cell","layout":{"type":"default"}} -->
			<div class="wp-block-group arch-cell">
				<!-- wp:html -->
				<div class="arch-cell__head"><span class="arch-cell__no">01 · Growth &amp; Marketing</span><span class="glyph glyph--market">▲</span></div>
				<!-- /wp:html -->
				<!-- wp:heading {"level":3,"className":"h3"} -->
				<h3 class="wp-block-heading h3">GSLEY Market</h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p>Predictable demand, brand authority, commercial systems.</p>
				<!-- /wp:paragraph -->
				<!-- wp:html -->
				<div class="tags"><span class="tag">Global enterprise</span><span class="tag">Hospitals &amp; foundations</span><span class="tag">Ambitious B2B &amp; B2C</span></div>
				<div style="margin-top:var(--sp-6)"><a href="/services/market/" class="open-link">Open ↗</a></div>
				<!-- /wp:html -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"arch-cell","layout":{"type":"default"}} -->
			<div class="wp-block-group arch-cell">
				<!-- wp:html -->
				<div class="arch-cell__head"><span class="arch-cell__no">02 · AI-Native Systems</span><span class="glyph glyph--digital">◇</span></div>
				<!-- /wp:html -->
				<!-- wp:heading {"level":3,"className":"h3"} -->
				<h3 class="wp-block-heading h3">GSLEY Digital</h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p>Digital infrastructure · agents · workflow architecture.</p>
				<!-- /wp:paragraph -->
				<!-- wp:html -->
				<div class="tags"><span class="tag">Hospitals</span><span class="tag">Foundations</span><span class="tag">Global enterprise</span></div>
				<div style="margin-top:var(--sp-6)"><a href="/services/digital/" class="open-link open-link--digital">Open ↗</a></div>
				<!-- /wp:html -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
