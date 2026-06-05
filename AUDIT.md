# GSLEY Digital — live-site audit & remediation register

Audit of **https://gsleydigital.com** by the full team (UI/UX, brand, product,
SEO, front-end, back-end, QA). Status legend:
**FIXED** = fixed on this branch (needs redeploy to go live) ·
**OWNER** = only you can complete (data/accounts/hosting) ·
**PHASE 3** = part of the new paid-tools workstream ·
**VERIFY** = re-check on the live site after redeploy.

> ⚠️ **Nothing on this branch is live until you redeploy.** EasyWP doesn't auto-deploy
> from GitHub. To publish these fixes: re-upload the updated `gsley-digital` theme and
> `gsley-diagnostics` plugin folders, run **GSLEY Leads → Setup → Import / update pages**,
> **Settings → Permalinks → Save**, then **purge the EasyWP cache**.

## Critical

| # | Area | Finding | Status |
|---|---|---|---|
| 1 | UI / brand | **Hero headline rendered at body size on every page** — the design tokens (`--fs-h1` → WP preset var) collapsed with no fallback, so the hero lost its display scale. | **FIXED** — literal fallbacks added to all 49 tokens + a hard `clamp(2.5rem,6vw,4.5rem)` hero rule. |
| 2 | Front-end / product | **Diagnostic tools unreachable** — `/tools/<slug>` all 404; the tools are the primary lead capture. | **FIXED** — importer now creates all 8 tool pages with their blocks. Re-run Setup → Import. |
| 3 | SEO | **`/diagnostic` 301 dead-ends** because its target tool page didn't exist. | **FIXED** — resolves once #2 is redeployed. |
| 4 | SEO | **`robots.txt` is the WordPress default** — no `Sitemap:` line, no explicit AI-crawler allows. Crawling isn't *blocked*, but our manifest isn't live. | **OWNER/VERIFY** — purge EasyWP cache; if still default, remove any physical `robots.txt` in the web root (or set it in EasyWP) so our filter serves. Our code is correct. |

## High

| # | Area | Finding | Status |
|---|---|---|---|
| 5 | Legal / brand | Privacy/Terms/Cookies are **public with bracketed `[FIELDS]`** (registered address, hosting/analytics, retention periods, DPO, etc.) visibly rendered. | **OWNER** — fill the real values (counsel-approved structure is done). Until then, consider setting them to Draft. |
| 6 | Back-end | **Dashboard only showed usage counts**, not what each visitor produced — limiting follow-up/conversion. | **FIXED** — per-lead detail (their answers/inputs + result), follow-up status, notes, and CSV export added. |
| 7 | Back-end | **Resend not configured** → report + contact emails won't send (leads still captured & logged `failed`). | **OWNER** — add the Resend API key + verified sending domain (Settings). |
| 8 | Product | No paid tools / online payment yet; you want **Social Media Audit ($10)** and **Digital Presence & ORM Audit ($20)**. | **PHASE 3** — building now (Paystack, own scoring engine, pay-by-email unlock). |

## Medium

| # | Area | Finding | Status |
|---|---|---|---|
| 9 | SEO | **Homepage `<title>` is "Gsley Digital AI | Marketing | PR | Health"** (the WordPress Site Title) rather than the brief's `Page — GSLEY Digital` pattern. Suggests the Site Title/another title source overrides per-page titles. | **OWNER/VERIFY** — set Site Title/tagline (Settings → General) and confirm no second SEO plugin is fighting our meta. |
| 10 | SEO / i18n | **TranslatePress (multilingual) is active** (added post-handoff). Risk of duplicate URLs/canonicals/hreflang interacting with our schema + sitemap. | **VERIFY** — confirm translated URLs carry correct canonical/hreflang and don't duplicate-index; add language URLs to the sitemap if translations are published. |
| 11 | A11y | Muted `--ink-300` informational labels failed WCAG AA contrast. | **FIXED earlier** — moved to `--ink-500`; re-verify live after redeploy. |
| 12 | Perf | EasyWP edge caching/minification can mangle CSS custom properties. | **MITIGATED** by the token fallbacks (#1); **VERIFY** fonts (woff2) + CSS load and Lighthouse ≥ 90 after redeploy. |

## To verify on the live site after redeploy
- Every `/tools/<slug>` loads and a diagnostic runs end-to-end (answer → score → email gate).
- Hero is display-scale on home, a service page, About, Contact, legal.
- `robots.txt` shows the AI allows + `Sitemap:` line; `/sitemap.xml` and `/llms.txt` still resolve.
- Submitting a tool with a real email creates a lead + a sent report (once Resend is keyed), visible in **GSLEY Leads → View**.
- No broken internal links (full crawl once tool pages exist).
- Validate JSON-LD in Google's Rich Results Test.

## Owner action list (consolidated)
1. **Redeploy** the updated theme + plugin and run Setup → Import → Permalinks Save → purge cache.
2. **Resend** API key + verified domain.
3. **Paystack** account + API keys (for the paid tools).
4. **Fill the legal `[FIELDS]`** (or set those pages to Draft until filled).
5. Confirm Site Title / check for a conflicting SEO plugin; decide on TranslatePress languages.
