<?php
/**
 * Title: Service Cards — Start Here
 * Slug: gsley-digital/service-cards
 * Categories: gsley, columns, call-to-action
 * Description: Grid of productised service cards, each linking to a service page. Layout and links locked; headings and copy editable.
 * Keywords: services, start here, sprint, cards, grid
 * Block Types: core/group
 * Inserter: true
 */

$gsley_services = array(
	array( 'Website Design &amp; Development', 'Conversion-engineered, AI-search ready, deployed in 4–8 weeks.', '/services/market/website-design', false ),
	array( 'Google Ads', 'Productised search and performance media. Pipeline within 30 days.', '/services/market/google-ads', false ),
	array( 'Email Marketing', 'Lifecycle systems, not blasts. Built on owned data.', '/services/market/email-marketing', false ),
	array( 'Social Media Marketing &amp; Management', 'Editorial cadence, paid amplification, governance baked in.', '/services/market/social-media-marketing', false ),
	array( 'Content Development', 'Thought-leadership content engineered for citation, not vanity metrics.', '/services/market/content-development', false ),
	array( 'AI Agent Deployment', 'Agentic workflows wired into the back office. Prebuilt patterns, not toy demos.', '/services/digital/ai-agent-deployment', true ),
);
?>
<!-- wp:group {"tagName":"section","className":"section section--bone","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section section--bone">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 03.5</span><span class="marker__rule"></span><span>Start here</span></div>
		<!-- /wp:html -->

		<!-- wp:group {"className":"start-head reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group start-head reveal">
			<!-- wp:paragraph {"className":"h2"} -->
			<p class="h2">Strategic outcomes begin with executable work.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"lede u-mt-4"} -->
			<p class="lede u-mt-4">Six places to begin — designed as Sprints, productised end-to-end, priced on engagement scope.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"start-grid reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group start-grid reveal">
			<?php foreach ( $gsley_services as $svc ) :
				list( $title, $desc, $url, $is_digital ) = $svc;
				$link_class = $is_digital ? 'open-link open-link--digital' : 'open-link';
				?>
			<!-- wp:group {"className":"start-card","layout":{"type":"default"}} -->
			<div class="wp-block-group start-card">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php echo $title; ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p><?php echo $desc; ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:html -->
				<a href="<?php echo $url; ?>" class="<?php echo $link_class; ?>">Begin sprint ↗</a>
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
