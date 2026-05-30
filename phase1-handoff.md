# GSLEY Digital — Phase 1 Hand-off & Phase 2 Build Brief

**Brand voice:** Engineered Precision. **Method:** The Archetype System™.
**Compiled:** end of Phase 1 (design + content). **Next:** Phase 2 build in Claude Code.

---

## 1. Status

Phase 1 delivered a complete, content-final, responsive wireframe of the lead-generation site plus a working reference tool and the spec for the rest. Everything is hand-built HTML/CSS in the "Engineered Precision" system (Source Serif 4 / Inter / JetBrains Mono; ink-on-paper + single teal accent; ink band = Market ▲, deep-teal band = Digital ◇). Pages are self-contained for standalone review; Phase 2 consolidates them onto one stylesheet and a WordPress theme.

**Built: 22 pages + 1 working tool + supporting docs.**

---

## 2. File → URL routing map

Static files use flat, descriptive names; the WordPress theme routes them to clean nested URLs.

| File | Production URL | Pillar |
|---|---|---|
| gsley-homepage.html | `/` (front-page.php) | — |
| about.html | `/about` | — |
| contact.html | `/contact` | — |
| services-market.html | `/services/market` | ▲ |
| services-digital.html | `/services/digital` | ◇ |
| tools.html | `/tools` | — |
| legal-privacy.html | `/legal/privacy` | — |
| service-website-design.html | `/services/market/website-design` | ▲ |
| service-google-ads.html | `/services/market/google-ads` | ▲ |
| service-email-marketing.html | `/services/market/email-marketing` | ▲ |
| service-social-media.html | `/services/market/social-media-marketing` | ▲ |
| service-content-development.html | `/services/market/content-development` | ▲ |
| service-demand-generation.html | `/services/market/demand-generation` | ▲ |
| service-b2b-sales-enablement.html | `/services/market/b2b-sales-enablement` | ▲ |
| service-strategic-partnerships.html | `/services/market/strategic-partnerships` | ▲ |
| service-institutional-brand-reputation.html | `/services/market/institutional-brand-and-reputation` | ▲ |
| service-thought-leadership.html | `/services/market/thought-leadership-programmes` | ▲ |
| service-marketing-automation.html | `/services/market/marketing-automation` | ▲ |
| service-ai-agent-deployment.html | `/services/digital/ai-agent-deployment` | ◇ |
| service-digital-transformation.html | `/services/digital/digital-transformation` | ◇ |
| service-data-infrastructure.html | `/services/digital/data-infrastructure` | ◇ |
| service-revenue-optimisation.html | `/services/digital/revenue-optimisation` | ◇ |
| tool-growth-diagnostic.html | `/tools/growth-diagnostic` | — |

---

## 3. Link-integrity audit (findings)

Every internal `href` was extracted and checked against built pages. The site is internally consistent **except** for the following — all expected, none accidental, each with an action:

**Referenced but not yet built — build in Phase 2:**
- `/services` — the nav "Services" item points here; no index exists. → Build a lightweight Services index (both pillars) **or** 301 to `/services/market`. *Recommend: build the index.*
- `/services/digital/automation-and-revops` — front-line Digital service (hub D-02, footer, homepage). → Build (inherits the service-page template + a named archetype, e.g. *The RevOps Spine Archetype™*).
- `/services/digital/ai-adoption-programmes` — front-line Digital service (hub D-03, footer). → Build (named archetype, e.g. *The Governed Adoption Archetype™*).
- `/legal/terms`, `/legal/cookies` — footer links. → Build from the privacy template; **counsel review required**.
- `/tools/{the other 7}` — specced in `diagnostics-tool-spec.md`, not built. → Build per spec (5 inherit the flagship engine; 2 are variant engines).

**Intentional redirects (see §4).**

**Footer-only, intentionally empty at launch — decide:**
- `/insights`, `/case-studies` — no content at launch (case studies omitted by decision). → **Recommend hiding these footer links until populated**, rather than linking to empty pages. Currently they would 404.

---

## 4. Redirect map (301)

| From | To | Reason |
|---|---|---|
| `/diagnostic` | `/tools/growth-diagnostic` | Legacy lead-magnet path (decision) |
| `/services/market/media-management` | `/services/market/institutional-brand-and-reputation#media-management` | Consolidated into Trust Asset Archetype™ (your call, option 2) |
| `/services/market/social-media-management` | `/services/market/social-media-marketing` | Combined page — **confirm** you don't want two separate pages |

---

## 5. Global SEO + AI-search config

**Metadata pattern (already embedded per page):** `<title>` ≤ 60 chars, format `<Page> — GSLEY <Pillar>`; meta description ≤ 155 chars; self-referencing canonical; `og:image` → `/assets/og/<slug>.png`; `twitter:card` = `summary_large_image`.

**Schema (JSON-LD) map — add in Phase 2 theme:**
- `Organization` + `WebSite` (+ SearchAction) — sitewide / homepage
- `Service` — every service page (provider = GSLEY, areaServed = Worldwide, serviceType = the archetype)
- `FAQPage` — homepage + each service/tool FAQ
- `BreadcrumbList` — every inner page (breadcrumbs already in markup)
- `SoftwareApplication` (or `WebApplication`) — each tool page
- `Person` — founder (About)

**robots.txt** (AI crawlers allowed, per decision):
```
User-agent: *
Allow: /
User-agent: GPTBot
Allow: /
User-agent: ClaudeBot
Allow: /
User-agent: PerplexityBot
Allow: /
Sitemap: https://gsleydigital.com/sitemap.xml
```

**sitemap.xml:** all built URLs at priority 0.5–1.0 (home 1.0, hubs/tools 0.8, services 0.7); exclude redirected and unbuilt URLs until live.

**llms.txt:** delivered (`llms.txt`) → deploy at `/llms.txt`. This is the unclaimed local advantage from the competitive teardown — Lagos competitors have none.

**AI-search answer blocks:** homepage + each FAQ carry citation-ready, ≤60-word answer prose. Keep these first in the DOM order of their section.

**OG images:** `generate-og-images.py` produces all 22 at 1200×630. Re-run with Source Serif 4 + Inter in `./fonts/` before launch (currently DejaVu fallback). Output → `/assets/og/`.

---

## 6. Anti-slop audit

Checked across all pages against the Anti-Slop Charter:
- **Banned phrases — none present:** no "unlock / leverage / supercharge / seamless / game-changing / elevate / in today's fast-paced world / take it to the next level."
- **No invented statistics or benchmarks.** The diagnostic uses qualitative bands, not "better than X%."
- **No fabricated proof:** zero fake client logos or testimonials; "Client roster — disclosed on request, under NDA" used instead.
- **No em-dash spam / no AI cadence:** sentences vary; declarative, editorial register held throughout.
- ⚠️ **Client must stand behind two claim types** before launch: delivery timelines ("4–8 weeks," "pipeline within 30 days") and the Google Ads "media paid into your own account" framing. These are commitments, not copy — confirm they match how you operate.

---

## 7. Accessibility annotations

Solved in the build: skip-link, semantic landmarks (`header/nav/main/section/footer`), logical heading order, `:focus-visible` rings, `prefers-reduced-motion` honoured, radio-group semantics + full keyboard control in the tool, labelled form fields, `aria-current` on nav, `aria-expanded` on the mobile toggle.

**To resolve in Phase 2:**
- ⚠️ **Contrast:** muted text token `--ink-300` (#8895A7) on paper (#FAF9F5) is ≈ 2.8:1 — **fails WCAG AA** for normal text. It's used for some small mono labels. Fix: use `--ink-500` (#4A5568, ≈ 7.6:1) for any *informational* small text; reserve `--ink-300` for decorative/large only. Audit each muted label.
- Founder photo needs real `alt` text (placeholder now).
- Tool: announce results screen via the existing `aria-live` region on completion.
- Verify teal accent (#16B8A6) is used for *non-text* or large elements on dark bands (it reads fine there); don't use it for small body text on paper.

---

## 8. Pre-launch QA checklist

**Content**
- [ ] Founder photo placed (`/assets/img/chukwudi-eze.jpg`) + alt text
- [ ] Confirm social URLs resolve (LinkedIn/X/Instagram/Facebook `gsleydigital`)
- [ ] Confirm timelines + Google Ads framing (see §6)
- [ ] Legal pages completed by counsel (privacy bracketed fields, terms, cookies)

**Build / technical**
- [ ] Consolidate inline CSS → single `gsley.css`; self-host fonts (woff2)
- [ ] Build missing pages (§3): services index, automation-and-revops, ai-adoption-programmes
- [ ] Build remaining 7 tools (per `diagnostics-tool-spec.md`)
- [ ] Hide or populate `/insights` + `/case-studies` footer links
- [ ] Implement redirects (§4)
- [ ] Resolve contrast + a11y items (§7)

**SEO / AI-search**
- [ ] JSON-LD per §5; validate in Rich Results Test
- [ ] sitemap.xml + robots.txt + llms.txt deployed
- [ ] Regenerate OG images with brand fonts; verify `og:image` per page
- [ ] Lighthouse: performance, a11y, SEO, best-practices ≥ 90

**Backend / lead capture**
- [ ] `/api/contact` → Resend (replaces unreliable WP mail)
- [ ] `/api/tools/{id}` → persist + Resend report (payload contract in spec §3)
- [ ] Admin dashboard: lead tracking + tool analytics
- [ ] DB schemas: `leads`, `tool_submissions`
- [ ] WhatsApp FAB number + pre-filled messages verified site-wide
- [ ] Spam protection on forms (honeypot / rate-limit)

**Pre-flight**
- [ ] Cross-browser + real-device responsive pass (375 / 768 / 1280)
- [ ] 404 page in the design system
- [ ] Analytics + consent banner live (cookie policy)

---

## 9. Decisions ledger (locked in Phase 1)

- Business line **+234 902 476 7079** (calls + WhatsApp); inbox **brief@gsleydigital.com**
- Logo: **"GSLEY DIGITAL" wordmark** on web; blue-wave mark for corporate docs only
- Pricing: **on request** across all tiers
- Case studies / testimonials: **omitted at launch**; NDA roster line instead
- AI crawlers: **allowed**
- Founder profile: **board-level register**, institution *types* only — no named employers/clients/publications
- Business Development folded into **Strategic Partnerships**; "Colocation" dropped
- Media Management folded into **Institutional Brand & Reputation** (option 2)
- Nav: **Home · Services · Tools · About · Contact**; Insights/Case Studies footer-only until populated
- Methodology: **The Archetype System™**; one named archetype per service
- Type: Source Serif 4 + Inter + JetBrains Mono (Tiempos = Phase 2 upgrade option)
- Schema: Organization now; LocalBusiness deferred until a publishable street address
- Contrast system: ink band = Market, deep-teal band = Digital; persistent WhatsApp FAB site-wide
- Email: **Resend** for all transactional + report delivery

---

## 10. Phase 2 build brief (Claude Code)

1. **Scaffold the WordPress theme** — `front-page.php` (home), `index.php`, page templates per the routing map. Preserve the static HTML as the build reference.
2. **Consolidate CSS** — extract the shared inline styles into one `gsley.css` of design tokens + components; self-host fonts.
3. **Complete the page set** — build the four missing pages (§3) in the existing template and voice; name their archetypes.
4. **Build the tools** — flagship engine is proven; create the other seven from `diagnostics-tool-spec.md` (5 config-only, 2 variant engines). One shared shell.
5. **Backend** — Resend-backed `/api/contact` and `/api/tools/{id}`; persistence; admin dashboard (leads + tool analytics); `leads` and `tool_submissions` schemas.
6. **SEO/AI layer** — JSON-LD, sitemap, robots, llms.txt, OG images (brand fonts), redirects.
7. **QA** — run §8 end to end; resolve the a11y/contrast items.

**Source artifacts in this package:** all 22 page files + `tool-growth-diagnostic.html`, `diagnostics-tool-spec.md`, `phase1-foundation.md`, `research-canonical.md`, `generate-og-images.py` (+ `/assets/og/`), `llms.txt`, and this brief.

---

## 11. Phase 1 sign-off

Phase 1 is content-final and structurally complete: the full page architecture, the locked design system, every service's named archetype, a working flagship diagnostic with a verified scoring engine, and the spec + SEO + hand-off scaffolding for Phase 2. The open items are catalogued above with actions — no silent gaps. Ready for the Claude Code build.
