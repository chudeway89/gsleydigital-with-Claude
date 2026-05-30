<?php
/**
 * Title: The Archetype System — Method Steps
 * Slug: gsley-digital/method-steps
 * Categories: gsley, columns, featured
 * Description: Deep-teal/ink band explaining The Archetype System™ as four steps. Layout and step numbers locked; intro, headings and copy editable.
 * Keywords: method, archetype, steps, process
 * Block Types: core/group
 * Inserter: true
 */

$gsley_steps = array(
	array( '01', 'Diagnose', 'A scored read-out of where the commercial system is leaking — revenue, reputation, or capacity.' ),
	array( '02', 'Architect', 'The named archetype mapped to your context, your data, and your constraints.' ),
	array( '03', 'Deploy', 'AI-native delivery against an explicit governance protocol — bias review, data residency, human override.' ),
	array( '04', 'Transfer', 'A documented operating manual your team owns and runs after we leave. No lock-in.' ),
);
?>
<!-- wp:group {"tagName":"section","className":"section section--ink","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section section--ink">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 06</span><span class="marker__rule"></span><span>The method</span></div>
		<!-- /wp:html -->

		<!-- wp:group {"className":"method-head reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group method-head reveal">
			<!-- wp:paragraph {"className":"h2 u-paper"} -->
			<p class="h2 u-paper">Most consulting is opinion, rented by the hour. <strong>The Archetype System™</strong> is the opposite.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"lede u-ink-300 u-mt-6"} -->
			<p class="lede u-ink-300 u-mt-6">Every engagement runs on a documented archetype — a named, repeatable method with defined inputs, sequence, and outputs. The archetype is yours to keep. When the engagement ends, the system stays, and your team runs it without us.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"steps reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group steps reveal">
			<?php foreach ( $gsley_steps as $step ) :
				list( $no, $title, $desc ) = $step; ?>
			<!-- wp:group {"className":"step","layout":{"type":"default"}} -->
			<div class="wp-block-group step">
				<!-- wp:html --><div class="step__no"><?php echo $no; ?></div><!-- /wp:html -->
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading"><?php echo $title; ?></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p><?php echo $desc; ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
			<?php endforeach; ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
