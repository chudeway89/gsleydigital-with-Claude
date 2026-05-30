# build-tasks.md — your step-by-step prompts for Claude Code

This is your worklist. **Do one task per session, in order.** After each one, read the summary, look at the changes, and click **Merge** if it looks right — then start the next. Don't run two at once.

Each task has three parts: **Paste this** (copy into Claude Code), **What to expect**, and **Before you merge**.

---

## Task 0 — Get oriented (changes nothing)

**Paste this:**
> Read phase1-handoff.md and CLAUDE.md in this repo. Summarise the build plan and the list of open items back to me in plain language. Do not change any files yet.

**What to expect:** A summary of the plan and the open items. No file changes.
**Before you merge:** Nothing to merge — just confirm its summary matches what you expect.

---

## Task 1 — Build the WordPress block theme

**Paste this:**
> Create a WordPress block theme (Full Site Editing) for this project. Put the GSLEY colours, the three fonts, and spacing from the HTML and phase1-foundation.md into theme.json, and self-host the fonts. Build the base templates (front page, generic page, index) and the header and footer as block template parts, matching the design in the HTML exactly. Show me a short plan first.

**What to expect:** A new block theme folder with `theme.json`, fonts, and base templates.
**Before you merge:** Check it created a block theme with theme.json and that the header/footer match the design.

---

## Task 2 — Turn the design into editable block patterns

**Paste this:**
> Now turn each recurring section from the HTML into a WordPress block pattern — the hero, service block, CTA band, FAQ, value ladder, and any others. Register them so they appear in the editor's pattern inserter. Lock the structure of each pattern so an admin can edit the text and images but cannot break the layout.

**What to expect:** A set of named block patterns, on-brand, available in the editor.
**Before you merge:** Confirm the patterns are locked (text editable, layout fixed) and look like the design.

---

## Task 3 — Build all the pages from the patterns

**Paste this:**
> Now build the actual pages by assembling the block patterns: the home page and every page in the routing map in phase1-handoff.md section 2, plus the not-yet-built pages listed in section 3 (the Services index, automation-and-revops, ai-adoption-programmes, and the legal Terms and Cookies pages). Use the Engineered Precision voice, name an archetype for each new service, and mark the legal copy for a lawyer to review.

**What to expect:** Each route exists as an editable page built from your patterns.
**Before you merge:** Click through a few pages; check they read in the right voice and match the design.

---

## Task 4 — Build the tools plugin (tools as blocks)

**Paste this:**
> Create a separate custom plugin for the interactive tools. Turn the flagship in tool-growth-diagnostic.html into a custom block, then build the other seven tools per diagnostics-tool-spec.md, reusing the same engine and shell — five are config-only, two are the variant engines (the calculator and the finder). Each tool should be a block an admin can insert from the editor, keep it answer-first, and keep the tools in the plugin, not the theme.

**What to expect:** A new plugin; eight tool blocks available in the editor.
**Before you merge:** Insert the Growth Diagnostic block on a test page and click through it.

---

## Task 5 — The backend (forms, email, dashboard)

**Paste this:**
> Now add the backend to the plugin: handle the contact form and the tool report delivery using Resend, create the gsley_leads and gsley_tool_submissions database tables, log delivery status, and build an admin dashboard page inside WP-admin for lead tracking and tool analytics. Use the payload contract in diagnostics-tool-spec.md section 3.

**What to expect:** Form handling, Resend email, database tables, and an admin dashboard page. This is the most technical task.
**Before you merge:** You can't fully test email until the site is on hosting — that's expected. Check it explains what it built and what's needed to run it.

---

## Task 6 — SEO and AI-search layer

**Paste this:**
> Now the SEO layer from phase1-handoff.md section 10 step 6: add the JSON-LD schema in section 5, plus sitemap.xml, robots.txt, llms.txt, the 301 redirects in section 4, and wire up the OG images. Run generate-og-images.py; the fallback fonts are fine for now.

**What to expect:** Schema on pages, plus the sitemap/robots/llms files and redirects.
**Before you merge:** Confirm robots.txt allows search and AI crawlers and that the section 4 redirects are included.

---

## Task 7 — Quality check and fixes

**Paste this:**
> Finally, work through the pre-launch checklist in phase1-handoff.md section 8, and fix the accessibility contrast issue in section 7 (replace muted --ink-300 text with --ink-500 for informational text). List anything on the checklist that still needs me, the owner, to do.

**What to expect:** A set of fixes plus a short list of things only you can finish.
**Before you merge:** Read the "needs the owner" list and keep it — that's your remaining to-do.

---

## After the build: going live

The site is built, but a live website needs **web hosting** to run. When you're ready, start a new task:
> Explain, step by step for a beginner, how to put this WordPress block theme and plugin live on hosting, including how to set up Resend so emails actually send.

---

## Reminders
- One task per session; review, then **Merge**.
- If a result confuses you, reply to Claude Code in plain English and ask it to explain.
- You can close the browser and come back — sessions keep running and you can check them from the Claude mobile app.
- Three things stay with you, not Claude Code: real contact details + founder photo, confirming the delivery-time / Google Ads claims, and getting the legal pages reviewed by a lawyer.
- Day to day after launch: you edit pages in the WordPress block editor by changing text in the patterns; you insert a tool by adding its block; you read leads in the admin dashboard.
