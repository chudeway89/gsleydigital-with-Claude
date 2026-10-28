# October 2026 redesign — what changed, and what needs you

This applies `redesign/fix-tasks.md` (Tasks A–O and I) to the WordPress theme and plugins.
Everything below is in the three upload files: `gsley-digital.zip` (theme),
`gsley-diagnostics.zip` and `gsley-seo.zip` (plugins).

## Sentences changed to remove unverified claims (Task O) — please review

| Page | Was | Now |
|---|---|---|
| Google Ads (hero) | "…turns existing intent into qualified pipeline — with a clear line from spend to booked revenue, usually inside 30 days." | "…turns existing intent into qualified pipeline — with a clear line from spend to booked revenue." |
| Google Ads (hero facts) | First pipeline: ~30 days | removed |
| Google Ads (outcomes) | "~30 days — To first qualified pipeline from launch." | "Traceable — A clear line from spend to qualified pipeline." |
| Google Ads (FAQ: How quickly will we see results?) | "Most accounts produce qualified pipeline within about 30 days of launch. The first weeks…" | "It depends on your market, your budget, and how much tracking is already in place, and we set expectations on the discovery call. The first weeks…" |
| Website Design (hero) | "Designed, built, and deployed in 4–8 weeks on The Conversion Architecture Archetype™" | "Designed, built, and deployed on The Conversion Architecture Archetype™" |
| Website Design (hero facts) | Timeline: 4–8 weeks | removed |
| Website Design (outcomes) | "4–8 wks — From kickoff to a deployed, instrumented site." | "Deployed — A live, instrumented site, with scope and timeline agreed in the proposal." |
| AI Adoption Programmes (hero facts) | Timeline: 8–12 weeks | removed |
| Automation & RevOps (hero facts) | Timeline: 6–10 weeks | removed |
| Social Media Management (hero facts) | Timeline: Ongoing | removed |
| Email Marketing (hero facts) | Build: Core flows in weeks | removed |
| Strategic Partnerships (hero facts) | Engagement: Deal Desk · retainer | Engagement: Retainer |
| Market hub (hero) | "Six front-line Sprints to begin; seven strategic engagements where the system compounds." | "Front-line Sprints to begin; strategic engagements where the system compounds." |
| Market hub | "Productised, scoped, and shipped fast. The doorway into the system." | "Productised and scoped end to end. The doorway into the system." |
| Market hub / Services (Website card) | "Conversion-engineered, AI-search ready, deployed in 4–8 weeks." | "Conversion-engineered and AI-search ready." |
| Market hub / Services (Google Ads card) | "Productised search and performance media. Pipeline within 30 days." | "Productised search and performance media, measured against pipeline." |
| Market hub | "…often a board-level mandate from day one." | "…often a board-level mandate from the start." |
| Digital hub / Services (AI Agent card) | "…Prebuilt patterns for hospitals, foundations, and enterprise — not toy demos." | "Agentic workflows wired into the back office, under explicit governance — for hospitals, foundations, and enterprise." |
| Digital hub | "Scoped deployments that produce measurable capacity, fast." | "Scoped deployments that produce measurable capacity." |
| Services index | "Productised, scoped, and shipped fast. If you're not sure…" | "Productised and scoped end to end. If you're not sure…" |
| Tools hub (AI Adoption — Organisation) | "→ Board-ready PDF" | "→ Board-ready read-out" (the tool emails a report; it does not produce a PDF) |
| Home (value ladder) | Four tiers incl. Toolkit (B2B Growth Stack, Reputation Playbook, Automation Architect Pack) | Three tiers: Free, Sprint, Retainer (Homepage v2 copy) |
| Home (start-here cards) | "…deployed in 4–8 weeks", "Pipeline within 30 days", "Prebuilt patterns, not toy demos" | Homepage v2 copy |
| Home / SEO FAQ | "…across four tiers — free, toolkit, sprint, and retainer." | "…across three tiers: free, sprint, and retainer." |
| Contact | Response-time and office-hours lines | Contact v2 copy ("We reply to every brief.") — no times promised |
| Service schema (SEO plugin) | Offer "Toolkit"; Retainer "Ongoing advisory and fractional executive engagement." | Toolkit removed; Retainer "Continuing advisory and delivery once the system is in place." |

Kept verbatim from Homepage v2, but worth a look: the value ladder's Free row says
"Diagnostics, briefings, scorecards." and the FAQ mentions "fractional executive retainers".
Change them in the editor if you don't offer briefings or fractional roles.

## Other copy changes

- **Counts are no longer typed.** Home ("Ten free diagnostics"), Tools (H1 and "the other nine"),
  About ("one of our ten free diagnostics"), Services ("Eleven doorways", per-pillar service counts)
  and Health ("Services: Three") come from the registry, via `[gsley_count]`.
- **Three pillars everywhere:** hero support line, answer paragraph, FAQ, footer tagline, Services,
  About, meta descriptions, llms.txt, Terms §1 ("three pillars — GSLEY Market, GSLEY Digital, and GSLEY Health").
- **Offices:** "headquartered in Lagos" / "Lagos-based" / "Lagos, Nigeria" → "offices in Lagos and Enugu, Nigeria"
  / "Lagos · Enugu, Nigeria · working worldwide" (hero, FAQ, footer, Contact, legal contact lines, llms.txt, schema).
- **Founder and credentials removed** from Home and About (portrait slot, bio, MCIPR / LBS / IBM / Google list,
  "Client roster — disclosed on request, under NDA"), and the founder `Person` schema is gone.
- **Section labels removed:** every "§ 0x — …" marker, "In one paragraph", "Operating method", the vertical
  "GSLEY · 02 PILLARS" text, and catalogue codes (M-01, D-02, C-01, T-03). Where a marker was a section's
  only heading, the section now has a real H2 ("The gap", "Outcomes", "Questions", …).
- **Legal pages:** only the contact line and the Terms pillar sentence changed. These pages still contain
  `[BRACKETED]` fields for you / your lawyer (registered entity and address, processors, retention periods,
  jurisdiction). They show on the live pages until filled in.

## Decisions only you can make

1. **Booking link:** paste a Cal.com or Calendly link in **GSLEY Leads → GSLEY Settings → Booking**.
   Until then, every "Book a Discovery Call" goes to the brief form on Contact.
2. **Office addresses:** add verified street addresses for Lagos and Enugu in **GSLEY Settings → Offices**.
   They then appear on Contact (with "Get directions") and in the Organization schema. Create or claim a
   Google Business Profile for each with the same name, address, and phone.
3. **Reinstating claims:** before putting back any timeline, response time, or toolkit product, confirm
   it's true in practice.
4. **Resend API key** (GSLEY Leads → Settings) — without it, briefs and reports are saved to the dashboard
   but no email is sent.
5. **Legal fields** in the three legal pages.
