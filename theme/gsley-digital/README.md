# GSLEY Digital — block theme

A WordPress block theme (Full Site Editing) for the GSLEY Digital lead-generation
site. Built in the "Engineered Precision" design system; ported from the Phase 1
static HTML reference without redesigning it.

## What's here (Phase 2 scaffold)

```
theme/gsley-digital/
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

| Pattern | Slug | Section |
|---|---|---|
| Hero — Thesis | `gsley-digital/hero` | §00 |
| Architecture — Two Pillars | `gsley-digital/architecture` | §01 |
| Pillars in Detail | `gsley-digital/pillars-detail` | §02 |
| Capability Map | `gsley-digital/capability-map` | §03 |
| Service Cards — Start Here | `gsley-digital/service-cards` | §03.5 |
| Value Ladder | `gsley-digital/value-ladder` | §04 |
| Tools Gallery | `gsley-digital/tools-grid` | §05 |
| The Archetype System — Method Steps | `gsley-digital/method-steps` | §06 |
| Founder Note | `gsley-digital/founder` | §07 |
| Proof — Credentials | `gsley-digital/proof` | §08 |
| FAQ — Questions | `gsley-digital/faq` | §09 |
| CTA Band — Closing | `gsley-digital/cta-band` | §10 |

`front-page.html` composes the homepage from these patterns in order. The
founder portrait is a locked placeholder (per `CLAUDE.md`, the photo + alt text
are a human to-do); swap it for a `core/image` block once supplied.

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
| `gsley-digital/page-legal-terms` | `/legal/terms` (new — counsel review) |
| `gsley-digital/page-legal-cookies` | `/legal/cookies` (new — counsel review) |

The three legal pages carry a visible **"Draft for legal review"** notice and
bracketed `[FIELDS]`; their body copy is editable so counsel can complete them.

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

> **Still to port (follow-up):** the 15 existing service detail pages
> (`/services/market/*` and the other `/services/digital/*`). The components
> and locking pattern they need already exist in this theme.

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
