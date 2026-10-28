# fix-tasks.md — apply the October 2026 review to the live WordPress build

Run one task per Claude Code session, in order. Each prompt is self-contained.
Reference files: `redesign/gsley.css`, `redesign/site.js`, `redesign/Homepage v2.html`, `redesign/Contact v2.html`, and `GSLEY Site Review.html`.

---

## Before Claude Code: WP-admin fixes (10 minutes, no code)

1. **Settings → General**: Site Title `GSLEY Digital`, Tagline `Growth & AI Studio`.
2. **SEO plugin → Titles**: page format `%title% — GSLEY Digital`; homepage `GSLEY Digital — Growth & AI Studio`; Pages og:type `website`; disable "reading time" for Pages.
3. **Media**: upload `redesign/assets/chukwudi-eze.jpg` to the media library for later use. It's already cleaned up (the other institution's lettering, the bust, and the phone are removed; 4:5, 730×912). It is not placed on any page for now.
4. **Plugins**: deactivate TranslatePress (single language) or hide its floating switcher.
5. **SEO per page**: write a 155-character meta description for Services, Tools, and every service page (they currently show breadcrumb text). Set the Services index og:image to its own file.

---

## Task A: Put the code in the repo (do this first)

> Add the live WordPress theme and every GSLEY plugin to this repository under wp-content/themes and wp-content/plugins. Don't change any code. Add a README section explaining how to deploy from Git. Show me the file list before committing.

---

## Task B: Single source of truth for site facts

> In the GSLEY plugin, add a "GSLEY Settings" admin page with fields for: email, phone (display + tel), WhatsApp number, WhatsApp pre-filled message, location line, office hours, booking URL (Cal.com/Calendly), and social links. Expose each as a block binding and a shortcode ([gsley key="email"]). Replace every hard-coded email, phone, WhatsApp link, location, and social link in the theme's template parts and patterns with these bindings. Use redesign/site.js `SITE.contact`, `SITE.socials`, and `SITE.bookingUrl` as the field list and default values.

## Task C: Counts and lists that generate themselves

> Make the plugin the registry for services and tools (slug, label, short label, pillar, front-line flag, and for tools: time, output, paid flag). Use redesign/site.js `SITE.services` and `SITE.tools` as the seed data. Then:
> 1. Add a [gsley_count type="free-tools|free-tools-others|all-tools|market-services|digital-services" format="word|number" cap="true"] shortcode / binding.
> 2. Replace the typed numbers on Home ("Eight diagnostics"), Tools (H1 "Ten tools", the closing "other seven"), About ("eight free diagnostics"), and Services ("Nine doorways", "Thirteen services", "Six services") with the binding.
> 3. Fix the Services index front-line list: the cards are numbered M-01…M-04, M-06. Renumber them from the registry, and remove the duplicate "RevOps" chip on the Digital card.
> 4. Build the footer service columns, the mobile menu, and the contact dropdown from the registry.

## Task D: Header, mobile menu, action bar, contrast

> Port redesign/gsley.css into the theme: tokens into theme.json (add --ink-400 #5F6B7C and --teal-ink #0E7A70 to the palette), components into the supplemental stylesheet. Then:
> 1. Remove the duplicate "Skip to content" (keep the theme's .skip-link).
> 2. Replace the mobile menu with the full-height panel from redesign/site.js: scroll lock, Escape closes it, links close it, 44px toggle with an X state, focus moves into the menu, booking and WhatsApp buttons at the bottom.
> 3. Below 768px, hide the floating WhatsApp button and show the bottom action bar after 480px of scroll (Contact page: primary button = Book a Discovery Call; elsewhere = Run the Growth Diagnostic).
> 4. Replace every use of --ink-300 on small text with --ink-400, and the hero "We engineer the gap." line with --ink-500. Make the ghost-button border #CFCBBE.
> 5. Add `.no-js .reveal{opacity:1}` so content can't stay hidden if JS fails.

## Task E: Homepage restructure

> Rebuild the front page to match redesign/Homepage v2.html exactly, using locked block patterns: §00 Hero, §01 Two pillars (merged; replaces Architecture, Pillars in detail, and Capability map), §02 Start here, §03 Free diagnostics (free tools, then "Live audits" for paid ones), §04 Method, §05 Value ladder, §06 FAQ (no founder section, no credentials section), closing CTA. Keep all copy verbatim. Don't add the capability progress bars back.

## Task F: Booking and the brief form

> Rebuild /contact to match redesign/Contact v2.html. Every "Book a Discovery Call" button uses the booking-URL setting; when it's empty, it links to /contact#brief. When it's set, show the embedded scheduler section on Contact. The brief form: dropdown built from the services registry and grouped by pillar; preselect from ?service=slug; add a phone field; inline per-field validation; sending/success/error states (error keeps the input and offers WhatsApp + email); honeypot field `website`; hidden `source` field. POST JSON to the existing /api/contact (Resend). Then update every service page's "Begin a sprint" button to /contact?service={slug}#brief.

## Task G: Tool pages render without JavaScript

> For every tool block, render the title (H1), one-paragraph description, time, output type, and FAQ in PHP (render.php), so the served HTML has real content. JavaScript only mounts the interactive part. Add WebApplication + FAQPage JSON-LD per tool. Then open all ten tool pages and run each one end-to-end, including the email report and the paid upgrade flow for the two paid audits. Report any that fail.

## Task H: Footer + legal

> Update the footer pattern to match redesign/site.js: socials under the contact block, a legal row (Privacy, Terms, Cookies) in footer-bottom, 36px minimum link height. Build /legal/terms and /legal/cookies from the privacy template and mark them for lawyer review. Make sure no footer link points to /insights or /case-studies.

---

## Task J: Remove all section labelling sitewide

> Across every template, template part, pattern, and page (Home, Services, Market, Digital, Health, every service page, Tools, every tool page, About, Contact, legal), remove:
> 1. Every section marker: the `.marker` element ("§ 00 — The thesis", "§ 01 — Two pillars", "§ A — The thesis", "§ 02 — The method", etc.).
> 2. Eyebrow labels used as headings: "In one paragraph", "Operating method", "Free diagnostics", "Start here — front-line Sprints", and the hero's vertical "GSLEY · 02 PILLARS" text.
> 3. Catalogue codes on cards and chips (M-01, D-02, T-03, C-01). Keep the pillar glyph (▲ ◇ ✚) as the only marker.
> Keep numbered steps inside a process (Diagnose 01–04, What happens next 01–03) and the value-ladder tier numbers; those are content, not labels. Where a removed marker was the only heading, make sure the section still has a real H2. Match redesign/Homepage v2.html, Contact v2.html, and Health v2.html. Then search all rendered HTML for "§" and confirm it finds nothing.

## Task K: Remove the Credentials and Founder sections from Home and About

> Delete the founder section completely from both Home and About (portrait, bio, credentials list, sign-off, LinkedIn link). Founder content isn't needed on the site at this stage. On Home and About, also delete the "Credentials, not claims" section, including everything in it (MCIPR, London Business School, Lagos Business School, IBM, Google) and the "Client roster — disclosed on request, under NDA" line. Remove the Person schema for the founder. On About, re-letter or renumber the remaining sections only if any visible labels are left (Task J removes them anyway). Keep the uploaded portrait in the media library for later use, but don't place it on any page.

## Task L: Create the GSLEY Health pillar

> Add a third pillar, GSLEY Health (glyph ✚, accent #A4473A, dark band #5A2219; tokens in redesign/gsley.css). Then:
> 1. Register three services in the services registry (seed: `SITE.services.health` in redesign/site.js): `pr-marketing-communications` (Healthcare PR, Marketing & Communications, The Clinical Trust Archetype™), `revenue-and-tariff-optimisation` (Revenue & Tariff Optimisation, The Tariff Integrity Archetype™), `thought-leadership` (Healthcare Thought Leadership, The Clinical Voice Archetype™).
> 2. Build the hub page /services/health to match redesign/Health v2.html exactly.
> 3. Build the three service pages at /services/health/{slug} from the existing service-page template (hero band, gap, method, included, outcomes, FAQ, CTA), using the copy for each service in Health v2.html. Each "Begin a sprint" links to /contact?service={slug}#brief.
> 4. Add Health everywhere the pillars appear: the Home pillars section (three cards, matching Homepage v2), the Services index (a third pillar card), About (Two pillars → Three pillars), the footer column, the contact dropdown (auto from registry), llms.txt, sitemap.xml, and Organization schema `knowsAbout`.
> 5. Update every "Two pillars" sentence to three, using the wording in Homepage v2 (hero support line, answer paragraph, first FAQ, footer tagline, meta descriptions).
> 6. Generate an OG image for /services/health with generate-og-images.py.

## Task M: One number for diagnostics everywhere

> Ten tools are published: eight question-based diagnostics, and two live audits (Social Media Audit, Digital Presence & ORM Audit) that give a free score with a paid detailed report. All ten start free, so the public number is **ten**. First, test all ten end-to-end. Unregister any tool that fails until it's fixed; the counts follow automatically. Then use the [gsley_count] binding from Task C for every mention:
> - Home: "Ten free diagnostics. Each ends in a scored read-out and a recommendation." Paid detail is mentioned once, under the two audits (see Homepage v2).
> - Tools H1: "Ten tools. One clear read-out each." Meta description: "Ten free diagnostics, each ending in a scored read-out and one clear recommendation." Closing line: "Start with the flagship. It tells you which of the other nine to run next."
> - About method step 01: "Often via one of our ten free diagnostics."
> Then search the site for "eight", "seven", "8 ", and "Eight" near "diagnostic" or "tool" and confirm none are left.

## Task N: Two offices (Lagos + Enugu) and local SEO

> GSLEY has offices in Lagos and Enugu. Lagos is the primary office for SEO; Enugu is a full second location.
> 1. Copy: replace every "headquartered in Lagos", "based in Lagos", "Lagos-based", and "Lagos, Nigeria" with the wording in redesign/Homepage v2.html and Contact v2.html ("offices in Lagos and Enugu, Nigeria" / "Lagos · Enugu, Nigeria · working worldwide"). Always list Lagos first. This applies to the hero paragraph, FAQ, footer, Contact, llms.txt, and meta descriptions.
> 2. GSLEY Settings: replace the single location field with an Offices repeater (city, street address, postcode, phone, Google Maps link). Leave it empty until the owner adds verified addresses.
> 3. Schema: one Organization with `address` = Lagos and `location` = [Lagos, Enugu], each a `Place` with a PostalAddress built from the Offices settings. Only output an office's address when its street address is filled in. Don't add LocalBusiness until addresses are verified.
> 4. Contact page: when addresses exist, show an Offices block with both cities, their addresses, and "Get directions" links. Until then, show only the city names.
> 5. Don't put city names in page titles. Don't create city landing pages unless there's real local content for them.

## Task O: Remove unverified claims sitewide

> Remove every timeline, response-time, result, or capability claim the business hasn't verified. Search all pages, patterns, service pages, tool pages, meta descriptions, and llms.txt for these and rewrite as in redesign/Homepage v2.html and Contact v2.html:
> - Timelines: "4–8 weeks", "6–10 weeks", "6–12 week", "Pipeline within 30 days", any "Timeline" fact on service-page heroes (remove that fact row entirely).
> - Response times: "within working hours", "reply within", "usually within", "Fastest reply — …Lagos time".
> - Unverified assets or offers: "Prebuilt patterns", "Fractional CDO", "Deal Desk", "Growth Engine Retainer", "Executive briefings", "Scorecards", and the Toolkit tier products (B2B Growth Stack, Reputation Playbook, Automation Architect Pack). The value ladder is now three tiers: Free, Sprint, Retainer. Update "four tiers" to "three tiers" everywhere.
> - Office hours: remove them unless the owner supplies them.
> - Any figure, percentage, or benchmark without a source.
> Then list every sentence you changed so the owner can review it.

## Task I: QA pass (run last)

> Test at 375, 768, and 1280px in Safari iOS, Chrome Android, and desktop Chrome/Firefox: menu, action bar, every form, every tool. Run Lighthouse on Home, Contact, one service page, and one tool page (target ≥ 90 on all four scores). Search the rendered HTML of every page for "[", "placeholder", "lorem", "TODO", and "WIREFRAME" and confirm none show up as visible text. List anything still needing the owner.

---

## Owner decisions
- Scheduler: choose Cal.com or Calendly and paste the link into GSLEY Settings.
- Offices: add verified street addresses for Lagos and Enugu in GSLEY Settings, and create or claim a Google Business Profile for each with the same name, address, and phone.
- Before reinstating any removed claim (timelines, response times, toolkit products), confirm it's true in practice.
