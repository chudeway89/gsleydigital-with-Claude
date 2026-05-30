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

## Backend

The plugin now includes the full data layer.

**Database tables** (created on activation; `dbDelta`, re-run on version bump):
- `{prefix}gsley_leads` — one row per capture event (contact brief or tool
  completion): source, name, email, organisation, need, message, tool, ip,
  user agent, and the notification-email delivery status.
- `{prefix}gsley_tool_submissions` — one row per completed diagnostic, linked to
  a lead: tool, overall/total, band, priority, the full result JSON, and the
  report-email delivery status (`pending` / `sent` / `failed` + Resend id/error).

**Tool reports.** On the email step the engine POSTs the
`diagnostics-tool-spec.md §3` payload to `POST /wp-json/gsley/v1/tools/{id}`
(validated: email + honeypot + per-IP rate limit). That fires
`do_action( 'gsley_diagnostics_submission', … )`, which persists the lead +
submission, builds the report (the §3 skeleton, tokenised per tool/engine), and
sends it via **Resend** — logging delivery status on the row.

**Contact form.** The theme's no-JS contact form posts to `/api/contact`; the
plugin intercepts that path, validates (honeypot + email + rate limit), stores
the lead, emails the brief to the notification inbox via Resend (reply-to the
enquirer), and redirects back to `/contact?sent=1` (POST/redirect/GET).

**Resend, not wp_mail.** All transactional email goes through Resend. The report
and contact emails share the on-brand HTML skeleton in `includes/report.php` /
`includes/contact.php`.

**Admin dashboard** (`GSLEY Leads` menu, `manage_options`):
- Summary cards: leads captured (+ unique emails), contact briefs, tool
  completions, reports sent (+ delivery rate / failures).
- Tool analytics: completions, average score (or estimate), reports sent, per tool.
- Recent leads table with source, contact details, tool/need, message snippet,
  and per-row email delivery status (failures show the Resend error on hover),
  paginated.
- **Settings** subpage: Resend API key, From address, notification inbox.

## What's needed to run it (on hosting)

1. **Activate the plugin** — this creates the two tables.
2. **Add a Resend API key** — either set `define( 'GSLEY_RESEND_API_KEY', '…' );`
   in `wp-config.php` (preferred), or paste it under **GSLEY Leads → Settings**.
3. **Verify the From domain/sender in Resend** and set the From address +
   notification inbox in Settings (defaults to `brief@gsleydigital.com`).
4. Leads are captured **even without a key** — only email sending is skipped, and
   the row is logged as `failed` with the reason, so nothing is lost.

> Email delivery genuinely can't be exercised here — it needs the site on
> hosting with a verified Resend domain and outbound HTTPS. The persistence,
> validation, report rendering, and dashboard are all in place and unit-checked;
> wiring the live key is the only remaining step.

## Try it

Activate the plugin, create a page, and insert any **GSLEY Diagnostics** block
(e.g. *The Gsley Growth Diagnostic™*), then view the page and click through.
The block shows a labelled placeholder in the editor; the live tool renders on
the front end.
