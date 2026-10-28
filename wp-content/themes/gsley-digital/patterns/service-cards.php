<?php
/**
 * Title: Start here — front-line Sprints
 * Slug: gsley-digital/service-cards
 * Categories: gsley, columns, call-to-action
 * Description: Six productised places to begin, each card linking to its service page. Layout and links locked; headings and copy editable.
 * Keywords: services, start here, sprint, cards, grid
 * Block Types: core/group
 * Inserter: true
 */

$gsley_start = array(
	array( 'Market', '▲', '', 'Website Design &amp; Development', 'Conversion-engineered and AI-search ready.', '/services/market/website-design/', '' ),
	array( 'Market', '▲', '', 'Google Ads', 'Productised search and performance media, measured against pipeline.', '/services/market/google-ads/', '' ),
	array( 'Market', '▲', '', 'Email Marketing', 'Lifecycle systems, not blasts. Built on owned data.', '/services/market/email-marketing/', '' ),
	array( 'Market', '▲', '', 'Social Media Marketing &amp; Management', 'Editorial cadence, paid amplification, governance baked in.', '/services/market/social-media-marketing/', '' ),
	array( 'Market', '▲', '', 'Content Development', 'Thought-leadership content engineered for citation, not vanity metrics.', '/services/market/content-development/', '' ),
	array( 'Digital', '◇', ' class="g--digital"', 'AI Agent Deployment', 'Agentic workflows wired into the back office, under explicit governance.', '/services/digital/ai-agent-deployment/', ' open-link--digital' ),
);
?>
<!-- wp:group {"tagName":"section","className":"section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:group {"className":"section-head reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group section-head reveal">
			<!-- wp:heading {"className":"h2"} -->
			<h2 class="wp-block-heading h2">Strategic outcomes begin with executable work.</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"lede"} -->
			<p class="lede">Six places to begin — designed as Sprints, productised end-to-end, priced on engagement scope.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
		<div class="start-grid reveal">
<?php foreach ( $gsley_start as $c ) : ?>
			<a href="<?php echo $c[5]; ?>" class="card"><span class="card__meta"><span><?php echo $c[0]; ?></span><span aria-hidden="true"<?php echo $c[2]; ?>><?php echo $c[1]; ?></span></span><h3><?php echo $c[3]; ?></h3><p><?php echo $c[4]; ?></p><span class="open-link<?php echo $c[6]; ?>">Begin sprint ↗</span></a>
<?php endforeach; ?>
		</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
