# GSLEY Digital — block theme

A WordPress block theme (Full Site Editing) for the GSLEY Digital lead-generation
site. Built in the "Engineered Precision" design system; ported from the Phase 1
static HTML reference without redesigning it.

## What's here (Phase 2 scaffold)

```
wp-content/themes/gsley-digital/
├── style.css            Theme header metadata
├── theme.json           Design tokens — colour, type, spacing, layout, self-hosted fonts
├── functions.php        Enqueues gsley.css + gsley.js; preloads above-the-fold fonts
├── templates/
│   ├── front-page.html  Home (header + post content + footer; homepage sections land later)
│   ├── index.html       Blog/archive fallback
│   └── page.html        Generic page
├── parts/
│   ├── header.html      Sticky brand header, primary nav, mobile nav, skip-link
│   └── footer.html      4-column footer + site-wide WhatsApp FAB
├── patterns/            Locked block patterns (contentOnly) — see below
└── assets/
    ├── css/gsley.css    Components theme.json can't express (header, nav, footer, buttons, FAB, reveal, sections, utilities)
    ├── css/editor.css   Editor-only overrides (show .reveal, un-stick header) for the Site Editor
    ├── js/gsley.js      Mobile-nav toggle + scroll-reveal (reduced-motion aware)
    └── fonts/*.woff2     Self-hosted Source Serif 4 / Inter / JetBrains Mono (latin + latin-ext)
```

## Block patterns

Every recurring homepage section is a named pattern in the **GSLEY Digital**
category of the inserter. Each pattern's outer container uses
`templateLock:"contentOnly"`, so admins can edit text, list items and the FAQ
but cannot move, remove, add or reorder blocks — the layout is fixed. Decorative
fragments (pillar glyphs ▲ ◇, section markers, capability bars, CTA buttons,
nav links) are locked `core/html`; headings, paragraphs, lists and the FAQ
(`core/details`) are editable core blocks.

| Pattern | Slug |
|---|---|
| Hero — Thesis | `gsley-digital/hero` |
| Three pillars | `gsley-digital/pillars` |
| Start here — front-line Sprints | `gsley-digital/service-cards` |
| Free diagnostics + live audits | `gsley-digital/tools-grid` |
| Method — The Archetype System™ | `gsley-digital/method-steps` |
| Value ladder — three tiers | `gsley-digital/value-ladder` |
| FAQ | `gsley-digital/faq` |
| Closing CTA | `gsley-digital/cta-band` |

`front-page.html` composes the homepage from these patterns in order, matching
`redesign/Homepage v2.html`. Site facts (email, phone, WhatsApp, booking link,
offices, socials) and counts are not typed into patterns: markup carries a
`data-bind` / `data-count` attribute or a `[gsley …]` shortcode, resolved on the
server by the GSLEY Diagnostics plugin from **GSLEY Leads → GSLEY Settings** and
the services/tools registry.

### Page patterns (whole routes)

Each route is also shipped as a full **page pattern** (`core/post-content`,
`Post Types: page`), built from the inner-page components and the same
`contentOnly` locking. They appear when creating a new Page.

| Pattern | Route |
|---|---|
| `gsley-digital/page-services` | `/services` (new index) |
| `gsley-digital/page-services-market` | `/services/market` |
| `gsley-digital/page-services-digital` | `/services/digital` |
| `gsley-digital/page-service-automation-revops` | `/services/digital/automation-and-revops` — *The RevOps Spine Archetype™* (new) |
| `gsley-digital/page-service-ai-adoption` | `/services/digital/ai-adoption-programmes` — *The Governed Adoption Archetype™* (new) |
| `gsley-digital/page-tools` | `/tools` |
| `gsley-digital/page-about` | `/about` |
| `gsley-digital/page-contact` | `/contact` |
| `gsley-digital/page-legal-privacy` | `/legal/privacy` |
| `gsley-digital/page-legal-terms` | `/legal/terms` (new) |
| `gsley-digital/page-legal-cookies` | `/legal/cookies` (new) |

The three legal pages are **counsel-approved** (the draft-review notice has been
removed and the last-updated date set to 30 May 2026). Their body copy is
editable. A few bracketed `[FIELDS]` remain for business facts the client must
supply before go-live — registered entity/address, jurisdiction, retention
periods, the processor/analytics list, the cookie table, and the DPO contact.

### Creating the Pages

These page patterns are full-bleed, so they use the **Full-width (pattern)
page** template (`templates/page-wide.html`) — no duplicate title or content
wrapper. To materialise every route at the correct nested slug in one step,
run the importer with WP-CLI from the WordPress root (theme active):

```
wp eval-file wp-content/themes/gsley-digital/bin/import-pages.php
```

It creates/updates the Pages, sets their hierarchy (e.g. `services › digital ›
automation-and-revops`), and assigns the full-width template. Idempotent —
re-running updates in place. Or create each Page by hand and insert the
matching pattern.

**Service detail pages** — all 17 are built as `page-service-*` patterns and
wired into the importer under their pillar parent:

- **Market:** website-design, google-ads, email-marketing,
  social-media-marketing, social-media-management, content-development,
  demand-generation, b2b-sales-enablement, strategic-partnerships,
  institutional-brand-and-reputation, thought-leadership-programmes,
  marketing-automation.
- **Digital:** ai-agent-deployment, automation-and-revops (*RevOps Spine™*),
  ai-adoption-programmes (*Governed Adoption™*), digital-transformation,
  data-infrastructure, revenue-optimisation.

Fifteen were ported from the Phase 1 reference HTML. **Social Media Management**
had no Phase 1 source — it is authored here (kept separate from Social Media
Marketing per decision) on *The Owned-Channel Archetype™*. Each carries its
named archetype in the hero meta; editable copy is the H1, hero lede, the gap
prose, the method archetype + lede, the FAQ and the closing line — dense grids
(phases, included scope, outcomes, companion tool, CTA buttons) are locked.

> **Note:** `media-management` is intentionally not a standalone page — it is
> consolidated into Institutional Brand & Reputation (`#media-management`).

## Design tokens

All tokens live in `theme.json` (see `phase1-foundation.md §3`). `gsley.css` aliases
the short token names from the design reference (`--ink-900`, `--sp-8`, …) to the
`--wp--preset--*` custom properties WordPress generates, so component CSS stays
identical to the approved design.

## Boundaries

- **Content lives here; application logic does not.** Tools, lead capture, email
  (Resend), the database, and the admin dashboard belong in a separate plugin.
- The `/insights` and `/case-studies` footer links are intentionally omitted until
  those pages have content (per `CLAUDE.md`).

## Not yet built (later Phase 2 tasks)

Service/tool/about/contact page templates, the diagnostics plugin, SEO/JSON-LD,
redirects.
