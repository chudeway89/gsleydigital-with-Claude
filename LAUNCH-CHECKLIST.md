# GSLEY Digital — pre-launch checklist status

Worked through `phase1-handoff.md §8`. Each item is marked **Done** (built in
this repo), **Owner** (only you can finish — accounts, real data, hosting, or a
business decision), or **Verify on hosting** (built, but needs a live server to
confirm).

## Content
- **Owner** — Founder photo (`/assets/img/chukwudi-eze.jpg`) + real alt text. The
  About + Founder patterns ship a styled placeholder; swap it for a `core/image`.
- **Owner** — Confirm the social URLs resolve (LinkedIn/X/Instagram/Facebook
  `gsleydigital`). They're wired in the footer, contact page, and Organization
  schema; they need to be real, live profiles.
- **Owner** — Confirm the delivery-time claims ("4–8 weeks", "pipeline within 30
  days") and the Google Ads "media paid into your own account" framing. These are
  commitments, not copy.
- **Done / Owner** — Legal pages are counsel-approved (draft banner removed, date
  set). **Owner** still fills the remaining business-fact `[FIELDS]`: registered
  entity & address, jurisdiction, retention periods, processor/analytics list,
  cookie table, DPO contact.

## Build / technical
- **Done** — Inline CSS consolidated into one `assets/css/gsley.css`; fonts
  self-hosted as woff2 (latin + latin-ext) in the theme.
- **Done** — Missing pages built: Services index, Automation & RevOps, AI
  Adoption Programmes (plus all 17 service pages, hubs, about, contact, legal).
- **Done** — All 8 diagnostic tools built (diagnostics plugin; one shell + Likert
  engine + calculator + finder).
- **Done** — `/insights` + `/case-studies` are omitted from the footer until they
  have content (not linked).
- **Done** — 301 redirects implemented (SEO plugin): `/diagnostic`,
  `/services/market/media-management`, `/services/digital/business-development`.
- **Done** — Accessibility/contrast (§7): informational `--ink-300` small text
  moved to `--ink-500` (≈7.6:1 on paper); tool results screen announces via an
  `aria-live` region; skip-link, focus styles, keyboard support, and
  reduced-motion are in place.

## SEO / AI-search
- **Done** — JSON-LD per §5 (SEO plugin). **Verify on hosting** — validate in
  Google's Rich Results Test after deploy.
- **Done** — `sitemap.xml`, `robots.txt` (search + GPTBot/ClaudeBot/PerplexityBot),
  and `llms.txt` are served by the SEO plugin. **Verify on hosting** — confirm
  each resolves once live.
- **Done (fallback) / Owner** — OG images generated (DejaVu fallback) and bundled
  with the SEO plugin. **Owner** — drop Source Serif 4 + Inter `.ttf` into
  `./fonts/`, re-run `python generate-og-images.py`, and copy the PNGs into
  `plugin/gsley-seo/assets/og/` for brand-font versions.
- **Verify on hosting** — Lighthouse ≥ 90 (performance, a11y, SEO, best-practices).

## Backend / lead capture
- **Done** — `/api/contact` → Resend (contact handler in the diagnostics plugin).
- **Done** — `/api/tools/{id}` → persist + Resend report.
- **Done** — Admin dashboard (GSLEY Leads): lead tracking + tool analytics.
- **Done** — DB schemas: `gsley_leads`, `gsley_tool_submissions`.
- **Done / Owner** — WhatsApp FAB + pre-filled messages are site-wide on
  `+234 902 476 7079`. **Owner** — confirm that number is correct.
- **Done** — Spam protection: honeypot + per-IP rate limit on the contact form
  and every tool submission.
- **Owner** — Add the Resend API key (`GSLEY_RESEND_API_KEY` in `wp-config.php`
  or the Settings page) and verify the sending domain in Resend. Until then leads
  are captured but emails are logged `failed`.

## Pre-flight
- **Verify on hosting** — Cross-browser + real-device responsive pass (375 / 768 /
  1280).
- **Done** — 404 page in the design system (`templates/404.html`).
- **Owner** — Analytics + consent banner live. Choose an analytics provider and a
  consent-banner tool, then reflect both in the Cookie Policy (it has bracketed
  placeholders ready).

---

## The short list — only you can finish these

1. **Founder photo + alt text.**
2. **Confirm real social profile URLs** resolve.
3. **Stand behind the delivery-time and Google Ads claims** (or reword them).
4. **Fill the legal `[FIELDS]`** with real values (entity, address, jurisdiction,
   retention periods, processors/analytics, cookie table, DPO).
5. **Resend:** add the API key and verify the sending domain.
6. **Confirm the WhatsApp/phone number** (+234 902 476 7079).
7. **Analytics + consent banner:** pick the tools, wire them, update the Cookie
   Policy to match.
8. **(Optional) Brand-font OG images:** add the `.ttf` files and re-run the
   generator.
9. **On hosting, verify:** Rich Results Test (schema), sitemap/robots/llms
   resolve, Lighthouse ≥ 90, and a cross-browser/device responsive pass.
