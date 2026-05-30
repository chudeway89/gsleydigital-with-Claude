<?php
/**
 * Title: Value Ladder
 * Slug: gsley-digital/value-ladder
 * Categories: gsley, text
 * Description: Four-tier value ladder from free diagnostics to bespoke retainer. Layout and tier numbers locked; stage names and copy editable.
 * Keywords: value ladder, tiers, pricing, offers
 * Block Types: core/group
 * Inserter: true
 */

$gsley_tiers = array(
	array( '01', 'Free', 'Audience', 'Diagnostics, briefings, scorecards. Top-of-funnel authority.', array( 'Growth Diagnostic', 'Executive briefings', 'Scorecards' ) ),
	array( '02', 'Toolkit', 'Self-serve', 'Productised playbooks, templates, and operating systems.', array( 'B2B Growth Stack', 'Reputation Playbook', 'Automation Architect Pack' ) ),
	array( '03', 'Cohort &amp; Sprint', 'Mid-ticket', 'Cohort programmes and 6–12 week productised engagements.', array( 'Demand Sprint', 'Partnership Sprint', 'AI Adoption Sprint' ) ),
	array( '04', 'Retainer', 'Bespoke', 'High-ticket advisory and fractional executive engagements.', array( 'Growth Engine Retainer', 'Fractional CDO', 'Deal Desk' ) ),
);
?>
<!-- wp:group {"tagName":"section","className":"section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 04</span><span class="marker__rule"></span><span>Value ladder</span></div>
		<!-- /wp:html -->

		<!-- wp:group {"className":"cap-head reveal u-block u-mb-12","layout":{"type":"default"}} -->
		<div class="wp-block-group cap-head reveal u-block u-mb-12">
			<!-- wp:paragraph {"className":"h2"} -->
			<p class="h2">Four tiers. Begin free, graduate at the speed of value, exit on a retainer.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ladder reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group ladder reveal">
			<?php foreach ( $gsley_tiers as $tier ) :
				list( $no, $stage, $label, $what, $egs ) = $tier;
				$eg_html = '';
				foreach ( $egs as $eg ) {
					$eg_html .= '<span>· ' . $eg . '</span>';
				}
				?>
			<!-- wp:group {"className":"ladder__row","layout":{"type":"default"}} -->
			<div class="wp-block-group ladder__row">
				<!-- wp:html --><span class="ladder__no"><?php echo $no; ?></span><!-- /wp:html -->
				<!-- wp:html --><div><div class="ladder__stage"><?php echo $stage; ?></div><div class="ladder__tier-label"><?php echo $label; ?></div></div><!-- /wp:html -->
				<!-- wp:paragraph {"className":"ladder__what"} -->
				<p class="ladder__what"><?php echo $what; ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:html --><div class="ladder__eg"><?php echo $eg_html; ?></div><!-- /wp:html -->
			</div>
			<!-- /wp:group -->
			<?php endforeach; ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
