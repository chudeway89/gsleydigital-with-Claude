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
└── assets/
    ├── css/gsley.css    Components theme.json can't express (header, nav, footer, buttons, FAB, reveal)
    ├── js/gsley.js      Mobile-nav toggle + scroll-reveal (reduced-motion aware)
    └── fonts/*.woff2     Self-hosted Source Serif 4 / Inter / JetBrains Mono (latin + latin-ext)
```

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

Homepage sections as locked block patterns, service/tool/about/contact page
templates, the diagnostics plugin, SEO/JSON-LD, redirects.
