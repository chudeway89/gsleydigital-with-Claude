# CLAUDE.md — GSLEY Digital website (Phase 2 build)

You are building the GSLEY Digital lead-generation website. **Read `phase1-handoff.md` before any task — it is the source of truth.** This file is the short version of the rules.

## What this is
A two-pillar productised-consultancy site: **GSLEY Market** (demand, brand, commercial — ink band, ▲) and **GSLEY Digital** (AI-native systems — deep-teal band, ◇). Primary goal: capture leads, mainly through eight interactive diagnostic tools. The site must be **editable by admins in the native WordPress block editor** — no third-party page builder.

## Architecture (do it this way)
- **A WordPress block theme (Full Site Editing).** Design tokens live in `theme.json`; the block editor and Site Editor make pages, header, and footer editable natively.
- **Recurring sections are custom block patterns** (hero, service block, CTA band, FAQ, value ladder, etc.). **Lock the structure** so admins can edit text and images but not break the layout.
- **Tools, lead capture, email, database, and the admin dashboard are a separate custom plugin** — never in the theme. Each diagnostic is exposed as a **custom block** (with a shortcode fallback) so an admin drops it onto any page from the inserter.
- This separation is deliberate: content in the theme/editor, application logic in the plugin. Changing one must never break the other.

## Sources
- The `.html` files in this repo are the **finished design reference** — match them exactly; do not redesign.
- File → URL routing: `phase1-handoff.md` §2. Tools spec: `diagnostics-tool-spec.md`. Design foundation: `phase1-foundation.md`.

## Design system
- Put the GSLEY palette, the three fonts, and spacing into **`theme.json`** so every block is on-brand by default.
- **Self-host** the fonts: Source Serif 4 (display), Inter (body), JetBrains Mono (labels) — woff2.
- Use a small supplemental stylesheet only for what `theme.json` can't express (e.g. the scroll-reveal effect). Single teal accent `#16B8A6`; Market = ink band, Digital = deep-teal band. Keep the persistent WhatsApp button site-wide.

## Voice — "Engineered Precision" (enforce on all copy)
Editorial, declarative, methodology-led. Documented method over opinion.
**Anti-Slop Charter — never use:** "unlock", "leverage", "supercharge", "seamless", "game-changing", "elevate", "take it to the next level", "in today's fast-paced world". No invented statistics. No fake client logos or testimonials. No benchmark claims ("better than X%") unless real data backs them. If you're about to generate filler, stop and write less.

## Tools
- The working reference is `tool-growth-diagnostic.html`. Its `CONFIG` + scoring engine is the pattern.
- In the plugin, turn the flagship into a custom block, then build the other seven per `diagnostics-tool-spec.md`: five are config-only on the same engine; two are variant engines (a numeric **calculator** and a multi-select **finder**). One shared shell.
- Every tool is **answer-first** (show the result, then ask for email) and emails its report via **Resend**.

## Backend
- All email goes through **Resend** (not WordPress mail).
- Endpoints handle the contact form and tool report delivery; payload contract in `diagnostics-tool-spec.md` §3.
- Persist leads + tool results in custom tables `gsley_leads` and `gsley_tool_submissions`. Build a native WP-admin dashboard for lead tracking and tool analytics. Log Resend delivery status.

## Working rules
- **One task at a time.** For anything large, show a plan and wait for approval before editing.
- When converting the design, **preserve the markup** — port it into block markup/patterns, don't rewrite the design.
- Lock pattern structure; leave text and images editable.
- Never link the empty `/insights` or `/case-studies` pages; hide those links until there's content.
- Honour accessibility: skip-link, focus styles, keyboard support, reduced-motion. Fix the contrast finding in `phase1-handoff.md` §7 (swap muted `--ink-300` text for `--ink-500`).

## Leave these to the human (do not invent)
- Whether the delivery-time and "Google Ads paid into your own account" claims are accurate.
- Final legal copy (privacy, terms, cookies) — these go to a lawyer.
- Real contact details, social links, and the founder photo + its alt text.
