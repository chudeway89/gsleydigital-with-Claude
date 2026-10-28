<?php
/**
 * Title: Page — Contact
 * Slug: gsley-digital/page-contact
 * Categories: gsley, page
 * Description: The /contact page: channels (WhatsApp, email, phone, offices), the brief form, the booking scheduler (shown once a booking link is set in GSLEY Settings) and what happens next. Layout locked; copy editable.
 * Block Types: core/post-content
 * Post Types: page
 * Inserter: true
 */
?>
<!-- wp:group {"tagName":"div","metadata":{"name":"Contact"},"templateLock":"contentOnly","layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:group {"tagName":"section","className":"page-hero","layout":{"type":"default"}} -->
<section class="wp-block-group page-hero">
<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
<div class="wp-block-group wrap">
<!-- wp:html -->
<nav class="crumb" aria-label="Breadcrumb"><a href="/">Home</a> › Contact</nav>
<!-- /wp:html -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Start a conversation.</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"lede"} -->
<p class="lede">Chat on WhatsApp for a quick question, send a brief if you know what you need, or book a discovery call to scope an engagement. Offices in Lagos and Enugu, working worldwide.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
<!-- wp:group {"tagName":"section","className":"section u-no-rule","layout":{"type":"default"}} -->
<section class="wp-block-group section u-no-rule">
<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
<div class="wp-block-group wrap">
<!-- wp:html -->
<div class="contact-grid">
  <div class="channels reveal">
    <a href="https://wa.me/2349024767079" data-bind="whatsapp" class="wa-card">
      <svg viewBox="0 0 32 32" width="36" height="36" fill="currentColor" aria-hidden="true"><path d="M16.04 3C9.4 3 4 8.4 4 15.04c0 2.12.55 4.16 1.6 5.98L4 29l8.2-1.55a12 12 0 0 0 3.84.63h.01c6.63 0 12.03-5.4 12.03-12.04C28.08 8.4 22.68 3 16.04 3zm5.44 14.5c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>
      <div><div class="t">Chat on WhatsApp</div><div class="s">The fastest way to reach us</div></div>
    </a>
    <div class="row"><span class="lbl">Email</span><a class="val" data-bind="email" href="mailto:brief@gsleydigital.com">brief@gsleydigital.com</a></div>
    <div class="row"><span class="lbl">Phone</span><a class="val" data-bind="phone" href="tel:+2349024767079">+234 902 476 7079</a></div>
    <div class="row"><span class="lbl">Offices</span><div class="val-block">[gsley_offices]</div></div>
  </div>

  <div class="reveal" id="brief">
    <div class="form-card">
      <form class="form" id="brief-form" action="/api/contact/" method="post" novalidate>
        <div class="form-row">
          <div class="field"><label for="brief-name">Name <span class="req" aria-hidden="true">*</span></label><input id="brief-name" name="name" type="text" required autocomplete="name" aria-describedby="brief-name-err"><span class="field__error" id="brief-name-err">Please add your name.</span></div>
          <div class="field"><label for="brief-email">Work email <span class="req" aria-hidden="true">*</span></label><input id="brief-email" name="email" type="email" required autocomplete="email" inputmode="email" aria-describedby="brief-email-err"><span class="field__error" id="brief-email-err">Please add a valid email address.</span></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="brief-org">Organisation</label><input id="brief-org" name="org" type="text" autocomplete="organization"></div>
          <div class="field"><label for="brief-phone">Phone or WhatsApp</label><input id="brief-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel"></div>
        </div>
        <div class="field"><label for="brief-need">What do you need?</label>
          <select id="brief-need" name="need">
            <option value="">Select one…</option>
            [gsley_service_options]
            <option value="not-sure">Not sure yet — advise me</option>
          </select>
        </div>
        <div class="field"><label for="brief-message">Tell us about the gap you're closing <span class="req" aria-hidden="true">*</span></label><textarea id="brief-message" name="message" required aria-describedby="brief-message-err"></textarea><span class="field__error" id="brief-message-err">A sentence or two is enough.</span></div>
        <div class="hp" aria-hidden="true"><label for="brief-website">Website</label><input id="brief-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
        <input type="hidden" name="source" id="brief-source">
        <p class="hint">We reply to every brief. By submitting you agree to our <a href="/legal/privacy/">privacy policy</a>.</p>
        <button type="submit" class="btn btn--solid" id="brief-submit">Send brief <span class="arrow">↗</span></button>
      </form>
      <div class="form-status form-status--ok" id="brief-ok" hidden tabindex="-1">
        <h3>Brief received.</h3>
        <p>Thank you. A real person will read it and reply. If it's urgent, message us on WhatsApp.</p>
        <div class="btns"><a href="https://wa.me/2349024767079" data-bind="whatsapp" class="btn btn--wa">Chat on WhatsApp</a><a href="/tools/growth-diagnostic/" class="btn btn--ghost">Run the Growth Diagnostic ↗</a></div>
      </div>
      <div class="form-status form-status--err" id="brief-err" hidden tabindex="-1">
        <h3>That didn't send.</h3>
        <p>Your message is still in the form. Try again, or reach us directly on WhatsApp or by email.</p>
        <div class="btns"><button type="button" class="btn btn--solid" id="brief-retry">Try again</button><a href="https://wa.me/2349024767079" data-bind="whatsapp" class="btn btn--wa">WhatsApp</a><a href="mailto:brief@gsleydigital.com" data-bind="email" class="btn btn--ghost">brief@gsleydigital.com</a></div>
      </div>
    </div>
  </div>
</div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:html -->
[gsley_booking]
<!-- /wp:html -->
<!-- wp:group {"tagName":"section","className":"section","layout":{"type":"default"}} -->
<section class="wp-block-group section">
<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
<div class="wp-block-group wrap">
<!-- wp:heading {"className":"h2 reveal u-mb-8"} -->
<h2 class="wp-block-heading h2 reveal u-mb-8">What happens next</h2>
<!-- /wp:heading -->
<!-- wp:html -->
<div class="next reveal">
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
