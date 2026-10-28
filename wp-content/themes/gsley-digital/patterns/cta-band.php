<?php
/**
 * Title: Closing CTA
 * Slug: gsley-digital/cta-band
 * Categories: gsley, call-to-action, banner
 * Description: Closing band with a declarative statement and two CTAs (Growth Diagnostic, Book a Discovery Call). Layout and buttons locked; statement copy editable.
 * Keywords: cta, closing, call to action, band, contact
 * Block Types: core/group
 * Inserter: true
 */
?>
<!-- wp:group {"tagName":"section","className":"section section--bone closing","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section section--bone closing">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:paragraph {"className":"display reveal"} -->
		<p class="display reveal">No jargon. <strong>Proven methodologies</strong>, data-backed precision, creative excellence — engineered for organisations that intend to define their category on the global stage.</p>
		<!-- /wp:paragraph -->
		<!-- wp:html -->
		<div class="closing__ctas reveal">
			<a href="/tools/growth-diagnostic/" class="btn btn--solid">Run the Growth Diagnostic <span class="arrow">↗</span></a>
			<a href="/contact/#brief" data-bind="book" class="btn btn--ghost">Book a Discovery Call <span class="arrow">↗</span></a>
		</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
