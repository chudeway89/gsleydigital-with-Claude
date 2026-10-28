<?php
/**
 * Title: FAQ — Questions
 * Slug: gsley-digital/faq
 * Categories: gsley, text
 * Description: Accordion FAQ built from editable Details blocks, carrying AI-search answer prose. Layout locked; questions and answers fully editable. Add FAQPage schema at the page level.
 * Keywords: faq, questions, accordion, answers
 * Block Types: core/group
 * Inserter: true
 */

$gsley_faqs = array(
	array( 'What is GSLEY Digital?', 'GSLEY Digital is a productised growth and AI studio with offices in Lagos and Enugu, Nigeria, serving institutions and ambitious B2B and B2C scale-ups worldwide. It operates three pillars — GSLEY Market for predictable demand and commercial systems, GSLEY Digital for AI-native operating systems, and GSLEY Health for healthcare PR, marketing, revenue, and thought leadership — delivered as one commercial system rather than disconnected campaigns.', true ),
	array( 'What is The Archetype System™?', 'The Archetype System™ is GSLEY&#8217;s operating method. Instead of opinion-based consulting, every engagement runs on a documented archetype — a named, repeatable method with defined inputs, sequence, and outputs. The archetype is transferred to the client at the end of the engagement, so the system stays in the organisation rather than leaving with the consultant.', false ),
	array( 'How is GSLEY different from a marketing agency?', 'A traditional agency sells deliverables and retains the method. GSLEY productises the method itself, ships AI-native delivery, and hands over a documented operating manual the internal team can run. The work is structured as named archetypes and an explicit value ladder — from free diagnostics to fractional executive retainers — rather than open-ended monthly retainers.', false ),
	array( 'Who does GSLEY work with?', 'Three buyers: institutional leaders at hospitals, foundations, and parastatals; enterprise growth leaders (CMOs and CROs) at venture- or PE-backed B2B businesses; and founders of ambitious Nigerian and West African scale-ups building toward category leadership.', false ),
	array( 'Where is GSLEY based?', 'GSLEY Digital has offices in Lagos and Enugu, Nigeria, and works with clients globally. Contact: [gsley_link type="email"] or [gsley_link type="phone"].', false ),
	array( 'How do I start working with GSLEY?', 'Run a free diagnostic for an instant scored read-out and a recommendation, or book a discovery call directly. Engagements are scoped from there across three tiers: free, sprint, and retainer.', false ),
);
?>
<!-- wp:group {"tagName":"section","className":"section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section">
	<!-- wp:group {"className":"wrap narrow","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap narrow">
		<!-- wp:heading {"className":"h2 reveal u-mb-8"} -->
		<h2 class="wp-block-heading h2 reveal u-mb-8">Frequently asked.</h2>
		<!-- /wp:heading -->

		<!-- wp:group {"className":"faq reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group faq reveal">
			<?php foreach ( $gsley_faqs as $faq ) :
				list( $q, $a, $open ) = $faq; ?>
			<!-- wp:details<?php echo $open ? ' {"showContent":true}' : ''; ?> -->
			<details class="wp-block-details"<?php echo $open ? ' open' : ''; ?>><summary><?php echo wp_kses_post( $q ); ?></summary>
			<!-- wp:paragraph -->
			<p><?php echo wp_kses_post( $a ); ?></p>
			<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->
			<?php endforeach; ?>
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
