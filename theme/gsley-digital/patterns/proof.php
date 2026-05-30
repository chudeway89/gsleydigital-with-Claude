<?php
/**
 * Title: Proof — Credentials
 * Slug: gsley-digital/proof
 * Categories: gsley, text
 * Description: Credential chips (no fake logos or testimonials) plus the NDA roster line. Layout locked; credential text editable.
 * Keywords: proof, credentials, trust, accreditation
 * Block Types: core/group
 * Inserter: true
 */

$gsley_creds = array(
	array( 'MCIPR', 'Chartered Institute of PR, UK' ),
	array( 'London Business School', 'Brand Management' ),
	array( 'Lagos Business School', 'Executive Programme' ),
	array( 'IBM', 'Enterprise Design Thinking' ),
	array( 'Google', 'Digital Marketing &amp; E-Commerce' ),
);
?>
<!-- wp:group {"tagName":"section","className":"section section--bone","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group section section--bone">
	<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
	<div class="wp-block-group wrap">
		<!-- wp:html -->
		<div class="marker"><span>§ 08</span><span class="marker__rule"></span><span>Credentials, not claims</span></div>
		<!-- /wp:html -->

		<!-- wp:html -->
		<div class="proof reveal">
			<?php foreach ( $gsley_creds as $cred ) :
				list( $org, $what ) = $cred; ?>
			<div class="proof__item"><span class="org"><?php echo $org; ?></span><span class="what"><?php echo $what; ?></span></div>
			<?php endforeach; ?>
		</div>
		<!-- /wp:html -->

		<!-- wp:paragraph {"className":"mono-tag u-mt-8"} -->
		<p class="mono-tag u-mt-8">Client roster — disclosed on request, under NDA.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
