# GSLEY Diagnostics (plugin)

The eight GSLEY interactive diagnostic tools, delivered as editor blocks. This
is a **separate plugin** — all tool/lead/email logic lives here, never in the
theme (per `CLAUDE.md`).

## What's here

```
plugin/gsley-diagnostics/
├── gsley-diagnostics.php     Plugin bootstrap: register blocks, assets, category
├── includes/
│   ├── configs.php           All 8 tool configs (one bank; the engine is shared)
│   ├── render.php            Block render callback → container + config JSON
│   └── rest.php              POST /wp-json/gsley/v1/tools/{id} (validate + hook)
└── assets/
    ├── tool.css              Scoped styles (.gsley-tool, gt-* classes, local tokens)
    ├── tool.js               Shared shell + 3 engines (likert | calculator | finder)
    └── editor.js             Registers the 8 blocks in the inserter (no build step)
```

## The eight blocks (category "GSLEY Diagnostics")

| Block | Engine |
|---|---|
| `gsley/tool-growth-diagnostic` | Likert (flagship) |
| `gsley/tool-ai-adoption-assessment-organisation` | Likert |
| `gsley/tool-ai-adoption-assessment-individual` | Likert |
| `gsley/tool-brand-authority-audit` | Likert |
| `gsley/tool-marketing-maturity-scorecard` | Likert |
| `gsley/tool-partnership-readiness-quiz` | Likert |
| `gsley/tool-revenue-leakage-calculator` | **Calculator** (numeric → figure) |
| `gsley/tool-automation-opportunity-finder` | **Finder** (multi-select → ranked list) |

Six are config-only on the shared Likert engine; two are the variant engines.
One shell (intro → steps → answer-first result → email gate) serves all three.

## Design principles honoured

- **Answer-first.** The score/figure/ranked list and the single recommendation
  render *before* the email gate. The email earns the detailed report.
- **One clear next step** mapped to a named service/archetype + book-a-call.
- **No fabricated benchmarks.** Bands are qualitative; the calculator is labelled
  an *estimate from your inputs*, capped at revenue.
- **Accessibility.** Radio-group semantics, number-key + Enter support, visible
  focus, `aria-live` regions, `prefers-reduced-motion` respected.
- **Self-contained.** Styles are scoped under `.gsley-tool` with local tokens
  and `gt-*` class names, so the plugin can't collide with the theme.
- **Instance-safe.** Multiple tools can sit on one page; each reads its own
  config and manages its own state.

## Backend contract (wired here, fulfilled by the data task)

On the email step the engine POSTs to `POST /wp-json/gsley/v1/tools/{id}` with
the payload from `diagnostics-tool-spec.md §3`. The endpoint validates (email,
honeypot, per-IP rate limit) and fires:

```php
do_action( 'gsley_diagnostics_submission', $submission, $tool );
```

The backend task hooks this to persist (`gsley_leads` / `gsley_tool_submissions`)
and send the report via **Resend**. Until then the endpoint acknowledges so the
answer-first flow works end to end.

## Try it

Activate the plugin, create a page, and insert any **GSLEY Diagnostics** block
(e.g. *The Gsley Growth Diagnostic™*), then view the page and click through.
The block shows a labelled placeholder in the editor; the live tool renders on
the front end.
