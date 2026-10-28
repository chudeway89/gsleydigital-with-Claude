<?php
/**
 * Title: Page — Services index
 * Slug: gsley-digital/page-services
 * Categories: gsley, page
 * Description: The /services index: the three pillars and every front-line Sprint. Cards and counts come from the services registry (GSLEY Diagnostics plugin). Layout locked; copy editable.
 * Block Types: core/post-content
 * Post Types: page
 * Inserter: true
 */
?>
<!-- wp:group {"tagName":"div","metadata":{"name":"Services index"},"templateLock":"contentOnly","layout":{"type":"default"}} -->
<div class="wp-block-group">
	<!-- wp:group {"tagName":"section","className":"page-hero","layout":{"type":"default"}} -->
	<section class="wp-block-group page-hero">
		<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap">
			<!-- wp:html --><nav class="crumb" aria-label="Breadcrumb"><a href="/">Home</a> › Services</nav><!-- /wp:html -->
			<!-- wp:heading {"level":1} -->
			<h1 class="wp-block-heading">Three pillars. One commercial system.</h1>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"lede"} -->
			<p class="lede">GSLEY Market engineers predictable demand and brand authority. GSLEY Digital builds the AI-native operating systems that release capacity. GSLEY Health runs public relations, marketing, revenue, and thought leadership for healthcare brands. Each runs on documented archetypes; together they compound. Begin with a front-line Sprint, or open a pillar to see the full catalogue.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","className":"section section--bone u-no-rule","layout":{"type":"default"}} -->
	<section class="wp-block-group section section--bone u-no-rule">
		<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap">
			<!-- wp:heading {"className":"h2 h2-gap reveal"} -->
			<h2 class="wp-block-heading h2 h2-gap reveal">The three pillars</h2>
			<!-- /wp:heading -->
			<!-- wp:html -->
			<div class="pillars">
				<article class="pillar pillar--market reveal">
					<div class="pillar__head"><span class="glyph glyph--market" aria-hidden="true">▲</span><div><h3 class="h3">GSLEY Market</h3><span class="pillar__sub">Growth &amp; Marketing</span></div></div>
					<p class="desc">Predictable demand, brand authority, and the commercial spine that converts attention into booked revenue. [gsley_count type="market-services" cap="true"] services across front-line Sprints and strategic engagements.</p>
					<div class="tags"><span class="tag">Website Design</span><span class="tag">Google Ads</span><span class="tag">Email</span><span class="tag">Social</span><span class="tag">Content</span><span class="tag">Demand Gen</span></div>
					<div class="pillar__foot"><a href="/services/market/" class="btn btn--solid">All Market services <span class="arrow">↗</span></a></div>
				</article>
				<article class="pillar pillar--digital reveal">
					<div class="pillar__head"><span class="glyph glyph--digital" aria-hidden="true">◇</span><div><h3 class="h3">GSLEY Digital</h3><span class="pillar__sub">AI-Native Systems</span></div></div>
					<p class="desc">AI-native operating systems — agents, automation, and data infrastructure — delivered under explicit governance. [gsley_count type="digital-services" cap="true"] services across front-line Sprints and strategic engagements.</p>
					<div class="tags"><span class="tag">AI Agents</span><span class="tag">Automation &amp; RevOps</span><span class="tag">AI Adoption</span><span class="tag">Data</span></div>
					<div class="pillar__foot"><a href="/services/digital/" class="btn btn--ghost">All Digital services <span class="arrow">↗</span></a></div>
				</article>
				<article class="pillar pillar--health reveal">
					<div class="pillar__head"><span class="glyph glyph--health" aria-hidden="true">✚</span><div><h3 class="h3">GSLEY Health</h3><span class="pillar__sub">Healthcare Growth</span></div></div>
					<p class="desc">Public relations, marketing, and commercial strategy for healthcare brands — hospitals, clinics, HMOs, health-tech, and pharma. [gsley_count type="health-services" cap="true"] services, each on a documented archetype.</p>
					<div class="tags"><span class="tag">PR &amp; Communications</span><span class="tag">Revenue &amp; Tariffs</span><span class="tag">Thought Leadership</span></div>
					<div class="pillar__foot"><a href="/services/health/" class="btn btn--health">All Health services <span class="arrow">↗</span></a></div>
				</article>
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","className":"section","layout":{"type":"default"}} -->
	<section class="wp-block-group section">
		<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap">
			<!-- wp:group {"className":"svc-head reveal","layout":{"type":"default"}} -->
			<div class="wp-block-group svc-head reveal">
				<!-- wp:heading {"className":"h2"} -->
				<h2 class="wp-block-heading h2">[gsley_count type="front-services" cap="true"] doorways into the system.</h2>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"className":"lede"} -->
				<p class="lede">Productised and scoped end to end. If you're not sure which to begin with, the Growth Diagnostic will tell you.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
			<!-- wp:html -->
			<div class="reveal">[gsley_service_cards front="true"]</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","className":"section section--ink","layout":{"type":"default"}} -->
	<section class="wp-block-group section section--ink">
		<!-- wp:group {"className":"wrap cta-band reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap cta-band reveal">
			<!-- wp:paragraph {"className":"display u-paper"} -->
			<p class="display u-paper">Not sure which doorway is yours? Run the Growth Diagnostic — it names the Sprint to start with.</p>
			<!-- /wp:paragraph -->
			<!-- wp:html -->
			<div class="cta-band__btns"><a href="/tools/growth-diagnostic/" class="btn btn--digital">Run the Growth Diagnostic ↗</a><a href="/contact/#brief" data-bind="book" class="btn btn--ghost">Book a Discovery Call ↗</a></div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
