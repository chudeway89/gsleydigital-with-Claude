# GSLEY Diagnostics — Tool Specification (Phase 1 → Phase 2 build spec)

**Status:** Flagware tool (`tool-growth-diagnostic.html`) is built and working; its `CONFIG` + engine is the reference. This document specs the remaining seven against the same pattern so they ship consistently in Phase 2.

---

## 1. Shared principles

- **Answer-first.** No sign-up wall to begin. The user completes the questions and sees their headline result (score, band, weakest dimension, one recommendation) *before* any email is requested.
- **Email earns the full report.** The detailed read-out + sequenced action plan is delivered by email — the lead-capture moment. The on-screen result is genuinely useful on its own, so the gate feels fair, not extractive.
- **One clear next step.** Every result resolves to a single prioritised recommendation mapped to a named service/archetype + the option to book a call. Never a menu of ten links.
- **No fabricated benchmarks.** We never claim "you scored better than 73% of companies" unless we hold real comparative data. Bands are described qualitatively.
- **Governed data.** Inputs + results POST to the backend; nothing sensitive in client storage. Reports sent via **Resend**. Consent + privacy link on the email step.
- **Accessibility.** Radio-group semantics, keyboard support (number keys select, Enter advances), visible focus, `prefers-reduced-motion` respected, live-region announcements on step change.

---

## 2. Shared scoring engine (Likert pattern)

Six of the eight tools use this exact engine. Only `CONFIG` changes.

```js
CONFIG = {
  id: "<tool-slug>",
  scale: [ {label, value:0} … {label, value:4} ],     // 5-point, 0–4
  dimensions: [ {id, label} … ],
  questions: [ {dim, text} … ],                         // ≥3 per dimension
  bands:     [ {min, max, name, blurb} … ],             // cover 0–100, no gaps
  recommendations: { <dimId>: {title, body, linkText, url} … }  // keyed to weakest dim
}
```

**Scoring:** per-dimension % = `sum(answers in dim) / (count × maxValue) × 100`; overall = mean of dimension %s; band = the band whose `[min,max]` contains overall; **priority = lowest-scoring dimension** (ties resolve to first in `dimensions` order); recommendation = `recommendations[priority]`.

**Verified:** all-min→0/first band; all-max→100/last band; midpoint→50; boundaries 40/41, 65/66, 85/86 partition cleanly.

**Two tools use variant engines** (documented in §4): the Revenue Leakage Calculator (numeric formula → figure) and the Automation Opportunity Finder (multi-select → ranked list).

---

## 3. API + report contract (Phase 2)

**Submit (on email step):**
`POST /api/tools/{toolId}`
```json
{
  "tool": "growth-diagnostic",
  "email": "you@org.com",
  "name": "optional",
  "answers": [0,3,4,…],
  "overall": 69,
  "band": "Systematised",
  "dimensions": [ {"id":"demand","pct":92}, … ],
  "priority": "capacity",
  "ts": "ISO-8601"
}
```
The client already assembles this as `window.__diagnosticResult` (see flagship). Backend validates, persists (lead + result row for the admin dashboard), renders the report template, sends via **Resend**, returns `{ok:true}`.

**Report template (email, shared skeleton):**
1. Header — tool name, the user's headline score + band.
2. What this means — the band blurb, in plain language.
3. Dimension breakdown — each dimension, its %, and a one-line interpretation.
4. Your priority — the weakest dimension expanded, with the matched archetype named.
5. A sequenced 90-day action plan — 3 phased moves (calculators/finders substitute their output here).
6. One CTA — book a discovery call (WhatsApp + email).
Editorial voice throughout; no slop, no invented stats.

---

## 4. The eight tools

### T-01 · The Gsley Growth Diagnostic™  — BUILT ✓
- **Output:** 0–100 score + band. **Engine:** Likert.
- **Dimensions (3 Q each, 12 total):** Demand · Conversion · Retention & Expansion · Capacity & Systems.
- **Bands:** Ad hoc (0–40) · Emerging (41–65) · Systematised (66–85) · Compounding (86–100).
- **Recommendation → service:** demand→Demand Generation · conversion→B2B Sales Enablement · retention→Revenue Optimisation · capacity→AI Agent Deployment.

### T-02 · AI Adoption Assessment — Organisation
- **Output:** board-ready maturity score + PDF. **Engine:** Likert (6 min, ~15 Q).
- **Dimensions (3 Q each):** Strategy · Data foundation · Governance · Capability & skills · Culture & adoption.
- **Bands:** Exploring · Experimenting · Operationalising · AI-native.
- **Representative Q:** "Leadership has a documented AI strategy tied to commercial outcomes." · "AI use is governed by an explicit policy covering bias, data residency, and human oversight." · "Our data is unified enough to support reliable AI outputs."
- **Recommendation → service:** strategy/culture→Digital Transformation · data→Data Infrastructure · governance/capability→AI Adoption Programmes · (high overall, narrow gap)→AI Agent Deployment.

### T-03 · AI Adoption Assessment — Individual
- **Output:** scored read-out + suggested learning path. **Engine:** Likert (4 min, ~10 Q).
- **Dimensions:** Awareness · Practical skills · Workflow integration · Judgement & governance.
- **Bands:** Curious · Capable · Fluent · Native.
- **Representative Q:** "I use AI tools in my daily workflow, not just occasionally." · "I can judge when an AI output is wrong or unsafe to use."
- **Recommendation:** learning-path guidance + AI Adoption Programmes (team rollout) where relevant.

### T-04 · Brand Authority Audit
- **Output:** 0–100 score + band. **Engine:** Likert (5 min, ~12 Q).
- **Dimensions:** Visibility · Credibility · Consistency · Share of voice.
- **Bands:** Invisible · Known · Respected · Authoritative.
- **Representative Q:** "Decision-makers in our market recognise our name unprompted." · "Our messaging is consistent across every channel and spokesperson." · "Our executives are quoted or cited in the conversations that matter."
- **Recommendation → service:** visibility/share-of-voice→Content Development or Social Media · credibility→Thought-Leadership · consistency→Institutional Brand & Reputation.

### T-05 · Revenue Leakage Calculator  — VARIANT ENGINE (calculator)
- **Output:** estimated annual leakage figure (currency) + breakdown. **Engine:** numeric inputs → formula. No Likert, no bands.
- **Inputs:** annual revenue · avg deal/customer value · lead→close conversion % · monthly churn % · % customers on expansion motion · avg discount %.
- **Formula (illustrative — confirm coefficients with real data before launch):**
  - Conversion leak = revenue × (benchmark_conv − actual_conv) capped ≥0
  - Retention leak = revenue × annualised(churn − target_churn)
  - Pricing leak = revenue × (discount% − floor_discount)
  - Expansion gap = revenue × (target_expansion% − actual_expansion%)
  - **Total = sum, shown with per-component bar breakdown.**
- **Result UX:** headline figure ("≈ ₦X/yr recoverable"), component bars, caveat that it's an estimate.
- **Recommendation → service:** Revenue Optimisation (primary); conversion-dominant→B2B Sales Enablement.
- ⚠️ **Honest-numbers rule:** label clearly as an *estimate from your inputs*, not a measurement. No fake precision.

### T-06 · Marketing Maturity Scorecard
- **Output:** 0–100 score + band. **Engine:** Likert (5 min, ~12 Q).
- **Dimensions:** Strategy · Channels · Data & measurement · Automation · Content.
- **Bands:** Ad hoc · Emerging · Systematised · Compounding (shared ladder language with the flagship).
- **Representative Q:** "We can attribute pipeline to specific marketing activity." · "Our lifecycle is automated on a unified view of the customer."
- **Recommendation → service:** strategy/channels→Demand Generation · data/automation→Marketing Automation · content→Content Development.

### T-07 · Automation Opportunity Finder  — VARIANT ENGINE (finder)
- **Output:** ranked list of automation opportunities (not a single score). **Engine:** multi-select + qualifiers → impact/effort ranking.
- **Inputs:** multi-select of workflow areas (e.g. lead routing, reporting, onboarding, invoicing, support triage, data entry…) + per selection: frequency (low/med/high) and pain (low/med/high).
- **Logic:** score each = frequency × pain weighting → sort desc → present top 3–5 as "highest-return opportunities," each with a one-line method and rough effort tag.
- **Recommendation → service:** Automation & RevOps (primary) · AI Agent Deployment (agentic candidates) · Data Infrastructure (if data-readiness is a blocker).

### T-08 · Partnership Readiness Quiz
- **Output:** 0–100 score + band. **Engine:** Likert (3 min, ~9 Q).
- **Dimensions:** Mandate · Value & offer · Structure & governance · Relationships.
- **Bands:** Not yet · Foundations in place · Ready to partner.
- **Representative Q:** "We have leadership mandate and budget to pursue a partnership." · "We can articulate a clear value exchange for the other party." · "We have a process for governing a multi-stakeholder agreement."
- **Recommendation → service:** Strategic Partnerships (all paths); low Mandate→advisory framing first.

---

## 5. Phase-2 build notes

- Reuse the flagship's engine verbatim for T-02, T-03, T-04, T-06, T-08 — only `CONFIG` differs. Complete each question bank (≥3 per dimension) and write band blurbs in the editorial voice.
- Build the two variant engines (T-05 calculator, T-07 finder) once each; both reuse the shell (header, progress, results layout, email step, FAB).
- Wire `POST /api/tools/{id}` → persist (admin dashboard: lead + tool + score + priority) → Resend report. One report template, tokenised per tool.
- Each tool page needs: metadata + `FAQPage`/`SoftwareApplication` schema, an OG image (generated by the Turn-12 OG script), and a matched-service cross-link.
- Accessibility + reduced-motion are already solved in the flagship; inherit them.

*Voice guard: band blurbs, recommendations, and report copy run through the Anti-Slop Charter. No invented statistics, no "unlock/leverage/supercharge," no benchmark claims we can't substantiate.*
