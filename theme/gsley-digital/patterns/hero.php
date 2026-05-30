<?php
/**
 * Title: Hero — Thesis
 * Slug: gsley-digital/hero
 * Categories: gsley, banner
 * Description: Full-width hero with the studio thesis, support copy, operating method, CTAs and an AI-search answer block. Layout locked; headings and copy editable.
 * Keywords: hero, thesis, banner, intro
 * Block Types: core/group
 * Inserter: true
 */
?>
<!-- wp:group {"tagName":"section","className":"hero wrap","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group hero wrap">
	<!-- wp:html -->
	<span class="hero__rotated">GSLEY · 02 PILLARS</span>
	<div class="marker"><span>§ 00</span><span class="marker__rule"></span><span>The Thesis</span></div>
	<!-- /wp:html -->

	<!-- wp:group {"className":"hero__thesis reveal","layout":{"type":"default"}} -->
	<div class="wp-block-group hero__thesis reveal">
		<!-- wp:heading {"level":1,"className":"display"} -->
		<h1 class="wp-block-heading display">Capability is common.<br>Commercial outcomes are not.<br><span class="soft">We engineer the gap.</span></h1>
		<!-- /wp:heading -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"hero__grid reveal","layout":{"type":"default"}} -->
	<div class="wp-block-group hero__grid reveal">
		<!-- wp:group {"className":"hero__support","layout":{"type":"default"}} -->
		<div class="wp-block-group hero__support">
			<!-- wp:paragraph -->
			<p>A productised growth and AI studio for institutions and ambitious scale-ups. Two pillars — predictable demand and AI-native operating systems — composed as one commercial system.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"hero__method","layout":{"type":"default"}} -->
		<div class="wp-block-group hero__method">
			<!-- wp:html --><span class="mono-tag">Operating method</span><!-- /wp:html -->
			<!-- wp:paragraph {"className":"u-fs-small u-ink-700"} -->
			<p class="u-fs-small u-ink-700">The Archetype System™. Documented archetypes — never opinions.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
		<div class="hero__ctas">
			<a href="/tools/growth-diagnostic" class="btn btn--solid">Run the Growth Diagnostic <span class="arrow">↗</span></a>
			<a href="/contact" class="btn btn--ghost">Book a Discovery Call <span class="arrow">↗</span></a>
		</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"answer-block reveal","layout":{"type":"default"}} -->
	<div class="wp-block-group answer-block reveal">
		<!-- wp:html --><span class="mono-tag">In one paragraph</span><!-- /wp:html -->
		<!-- wp:paragraph -->
		<p>GSLEY is a productised growth and AI studio headquartered in Lagos, serving institutions and B2B and B2C scale-ups globally. It operates two pillars: GSLEY Market — predictable demand, brand authority, and commercial systems — and GSLEY Digital — AI-native operating systems, agent deployment, and workflow architecture. Its method is documented as named archetypes rather than opinion-based consulting, and every engagement ships with AI-native delivery and a transferable operating manual.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
