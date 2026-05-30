<?php
/**
 * Title: Page — Contact
 * Slug: gsley-digital/page-contact
 * Categories: gsley, page
 * Description: The /contact page — direct channels, brief form, and what-happens-next. Layout locked; copy editable. The form posts to the plugin's /api/contact (Resend) endpoint.
 * Block Types: core/post-content
 * Post Types: page
 * Inserter: true
 */
?>
<!-- wp:group {"tagName":"div","metadata":{"name":"Contact page"},"templateLock":"contentOnly","layout":{"type":"default"}} -->
<div class="wp-block-group">
	<!-- wp:group {"tagName":"section","className":"page-hero","layout":{"type":"default"}} -->
	<section class="wp-block-group page-hero">
		<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap">
			<!-- wp:html --><div class="crumb"><a href="/">Home</a> › Contact</div><!-- /wp:html -->
			<!-- wp:heading {"level":1} -->
			<h1 class="wp-block-heading">Start a conversation.</h1>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"lede"} -->
			<p class="lede">Chat on WhatsApp for a quick question, send a brief if you know what you need, or book a discovery call to scope an engagement. Lagos-based, working worldwide.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","className":"section","layout":{"type":"default"}} -->
	<section class="wp-block-group section">
		<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap">
			<!-- wp:html -->
			<div class="contact-grid">
				<div class="channel">
					<div class="marker" style="margin-bottom:0"><span>§ 01</span><span class="marker__rule"></span><span>Direct channels</span></div>
					<a href="https://wa.me/2349024767079?text=Hi%20GSLEY%2C%20I%27d%20like%20to%20talk%20about%20a%20project." class="wa-card" target="_blank" rel="noopener">
						<svg viewBox="0 0 32 32" width="36" height="36" fill="currentColor" aria-hidden="true"><path d="M16.04 3C9.4 3 4 8.4 4 15.04c0 2.12.55 4.16 1.6 5.98L4 29l8.2-1.55a12 12 0 0 0 3.84.63h.01c6.63 0 12.03-5.4 12.03-12.04C28.08 8.4 22.68 3 16.04 3zm0 21.9c-1.2 0-2.39-.2-3.51-.6l-.25-.1-4.87.92.93-4.74-.16-.26a9.86 9.86 0 0 1-1.51-5.22c0-5.46 4.45-9.9 9.92-9.9 2.65 0 5.13 1.03 7 2.9a9.82 9.82 0 0 1 2.9 7c0 5.47-4.45 9.9-9.92 9.9zm5.44-7.4c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>
						<div><div class="t">Chat on WhatsApp</div><div class="s">Fastest reply — usually within working hours, Lagos time</div></div>
					</a>
					<div class="channel-row"><span class="lbl">Email</span><a href="mailto:brief@gsleydigital.com" class="val">brief@gsleydigital.com</a></div>
					<div class="channel-row"><span class="lbl">Phone</span><a href="tel:+2349024767079" class="val">+234 902 476 7079</a></div>
					<div class="channel-row"><span class="lbl">Studio</span><span class="val">Lagos, Nigeria · working worldwide</span></div>
					<div><div class="lbl" style="font-family:var(--font-mono);font-size:var(--fs-mono);letter-spacing:.1em;text-transform:uppercase;color:var(--ink-500);margin-bottom:var(--sp-3)">Follow</div>
						<div class="socials"><a href="https://www.linkedin.com/company/gsleydigital/">LinkedIn</a><a href="https://x.com/gsleydigital/">X</a><a href="https://www.instagram.com/gsleydigital/">Instagram</a><a href="https://web.facebook.com/gsleydigital/">Facebook</a></div>
					</div>
				</div>
				<div>
					<div class="marker" style="margin-bottom:var(--sp-6)"><span>§ 02</span><span class="marker__rule"></span><span>Send a brief</span></div>
					<form class="form" action="/api/contact" method="post">
						<input type="text" name="company_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
						<div class="field"><label for="name">Name</label><input id="name" name="name" type="text" required autocomplete="name"></div>
						<div class="field"><label for="email">Work email</label><input id="email" name="email" type="email" required autocomplete="email"></div>
						<div class="field"><label for="org">Organisation</label><input id="org" name="org" type="text" autocomplete="organization"></div>
						<div class="field"><label for="need">What do you need?</label>
							<select id="need" name="need">
								<option value="">Select one…</option>
								<option>Website design &amp; development</option>
								<option>Google Ads / paid media</option>
								<option>Email marketing</option>
								<option>Social media</option>
								<option>Content development</option>
								<option>AI agent deployment</option>
								<option>Automation &amp; RevOps</option>
								<option>Strategic partnerships</option>
								<option>Institutional brand &amp; reputation</option>
								<option>Not sure yet — advise me</option>
							</select>
						</div>
						<div class="field"><label for="message">Tell us about the gap you're closing</label><textarea id="message" name="message" placeholder="A few sentences on where you are and where you want to be."></textarea></div>
						<p class="hint">We reply to every brief. For a faster response, use WhatsApp. By submitting you agree to our <a href="/legal/privacy" style="text-decoration:underline">privacy policy</a>.</p>
						<button type="submit" class="btn btn--solid">Send brief <span class="arrow">↗</span></button>
					</form>
				</div>
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","className":"section section--bone","layout":{"type":"default"}} -->
	<section class="wp-block-group section section--bone">
		<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
		<div class="wp-block-group wrap">
			<!-- wp:html --><div class="marker"><span>§ 03</span><span class="marker__rule"></span><span>What happens next</span></div><!-- /wp:html -->
			<!-- wp:html -->
			<div class="next">
				<div class="next__step"><div class="no">01</div><h3>We read the brief</h3><p>A real person reviews what you sent — not an autoresponder. You hear back, with a first read on whether we're the right fit.</p></div>
				<div class="next__step"><div class="no">02</div><h3>A scoping call</h3><p>30 minutes to map the gap to an archetype and a tier — free diagnostic, sprint, or retainer. No pitch theatre.</p></div>
				<div class="next__step"><div class="no">03</div><h3>A written proposal</h3><p>Scope, deliverables, timeline, and the named archetype that will run the engagement. You see the method before you commit.</p></div>
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
	</section>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
