# Phase 1 Foundation — Sitemap · Design Tokens · Schema Map
## GSLEY Digital · Turn 5 of 12

*Locks the structural decisions every subsequent build turn (Turns 6–12) depends on. No content drafted in this file — it is the spec the content sits inside.*

---

## 1 · Decisions locked

| # | Decision | Status |
|---|---|---|
| 1 | Business line +234 902 476 7079; business inbox brief@gsleydigital.com | Locked Turn 2 |
| 2 | Logo Option A — "GSLEY DIGITAL" wordmark on website; blue wave for corporate docs only | Locked Turn 2 |
| 3 | Pricing — all tiers, price on request | Locked Turn 2 |
| 4 | Case studies — placeholder structure only, no fakes | Locked Turn 2 |
| 5 | AI crawlers — allow | Locked Turn 2 |
| 6 | Founder profile — board-level register, no employer names | Locked Turn 2 |
| 7 | Service IA — 6 front-line surfaced, 13 strategic retained as full pages | Locked Turn 2 |
| 8 | Business Development → folded under `/services/market/strategic-partnerships` | Locked Turn 3 |
| 9 | "Colocation" dropped from the Strategic Partnerships name | Locked Turn 3 |
| 10 | Navigation: Home · Services · Tools · About · Contact | Locked Turn 3 |
| 11 | Insights / Case Studies not in nav until populated | Locked Turn 3 |
| 12 | **Methodology name: The Archetype System™** | **Locked this turn** |
| 13 | Typography: Source Serif 4 (display) + Inter (body) for launch | Locked this turn |
| 14 | LocalBusiness schema: skipped at launch (Organization schema only) | Locked this turn |
| 15 | `/diagnostic` → 301 redirect to `/tools/growth-diagnostic` (canonical) | Locked this turn |

---

## 2 · Final sitemap

URL conventions: lowercase, hyphen-separated, no trailing slash, every URL serves one canonical resource. Hierarchy is two pillars × service depth, with the tools hub running parallel as its own SEO surface.

```
/                                                       Homepage
/about                                                  Studio thesis, founder, methodology, values
/services                                               Hub — both pillars
│
├── /services/market                                    Market pillar hub
│   │
│   ├── FRONT-LINE (surfaced on homepage START HERE block)
│   ├── /services/market/website-design
│   ├── /services/market/google-ads
│   ├── /services/market/email-marketing
│   ├── /services/market/social-media-marketing
│   ├── /services/market/social-media-management
│   ├── /services/market/content-development
│   │
│   └── STRATEGIC (full pages, upsell surface)
│       ├── /services/market/demand-generation
│       ├── /services/market/b2b-sales-enablement
│       ├── /services/market/strategic-partnerships         (absorbs former /digital/business-development)
│       ├── /services/market/institutional-brand-and-reputation
│       ├── /services/market/thought-leadership-programmes
│       ├── /services/market/marketing-automation
│       └── /services/market/media-management
│
└── /services/digital                                   Digital pillar hub
    │
    ├── FRONT-LINE
    ├── /services/digital/ai-agent-deployment
    ├── /services/digital/automation-and-revops
    ├── /services/digital/ai-adoption-programmes
    │
    └── STRATEGIC
        ├── /services/digital/digital-transformation
        ├── /services/digital/data-infrastructure
        └── /services/digital/revenue-optimisation

/tools                                                  Hub — 8 interactive diagnostics
├── /tools/ai-adoption-assessment-individual
├── /tools/ai-adoption-assessment-organisation
├── /tools/growth-diagnostic                            (canonical for /diagnostic legacy redirect)
├── /tools/brand-authority-audit
├── /tools/revenue-leakage-calculator
├── /tools/marketing-maturity-scorecard
├── /tools/automation-opportunity-finder
└── /tools/partnership-readiness-quiz

/insights                                               Footer link only at launch
└── /insights/{slug}                                    Article template (ready, unpopulated)

/case-studies                                           Footer link only at launch
└── /case-studies/{slug}                                "In development — request anonymised proof"

/contact                                                Form + WhatsApp + email + phone

/legal/privacy                                          NDPR + GDPR compliant
/legal/terms
/legal/cookies

/sitemap.xml                                            Auto-generated
/robots.txt                                             Generated (AI crawlers allowed per decision)
/llms.txt                                               Manifest for LLM crawlers

REDIRECTS
/diagnostic               → 301 → /tools/growth-diagnostic
/services/digital/business-development  → 301 → /services/market/strategic-partnerships
```

**Total live URLs:** 5 top-level + 2 pillar hubs + 19 service pages + 8 tools + 3 legal + 2 archive hubs = **39 indexable URLs** at launch.

---

## 3 · Design tokens (CSS custom properties — locked)

### 3.1 Colour
Verified against the existing PDF site palette; teal accent kept exactly because it is already brand-recognisable from the Digital pillar marker.

```css
/* Ink — primary text and dark surfaces */
--ink-900: #0F1B2D;        /* primary text, dark surfaces, accent-market */
--ink-700: #1F2C3F;
--ink-500: #4A5568;
--ink-300: #8895A7;

/* Paper — primary surfaces */
--paper:   #FAF9F5;        /* primary background */
--bone:    #F1EFE7;        /* alt-section background, capability-map background */
--line:    #E5E2D8;        /* dividers, hairlines */

/* Pillar accents */
--accent-market:  #0F1B2D; /* triangle ▲ — same as ink-900 by design */
--accent-digital: #16B8A6; /* diamond ◇ — teal, preserved from existing site */

/* State */
--danger:  #C0392B;
--success: #15803D;
--warning: #B7791F;        /* added — needed for tool feedback states */

/* Focus — accessibility */
--focus:   #16B8A6;        /* WCAG 2.2 visible focus, matches digital accent */
--focus-ring-width: 2px;
--focus-ring-offset: 2px;
```

### 3.2 Typography
```css
--font-display: "Source Serif 4", "Source Serif Pro", Georgia, serif;
--font-body:    "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
--font-mono:    "JetBrains Mono", "IBM Plex Mono", ui-monospace, monospace;
```
**Self-host both Source Serif 4 and Inter** as `woff2` from Google Fonts open-source release. Performance budget requires no third-party font CDN. Subset to Latin + Latin-Ext. Preload the display weights used above the fold (400, 600); load remaining weights deferred.

### 3.3 Type scale — fluid
```css
--fs-h1:    clamp(2.5rem,  6vw,   4.5rem);
--fs-h2:    clamp(1.75rem, 4vw,   2.75rem);
--fs-h3:    clamp(1.25rem, 2.5vw, 1.5rem);
--fs-body:  clamp(1rem,    1.1vw, 1.0625rem);
--fs-small: 0.875rem;
--fs-mono:  0.75rem;

--lh-tight: 1.1;     /* display */
--lh-snug:  1.3;
--lh-base:  1.55;    /* body */
--lh-loose: 1.7;
```

### 3.4 Spacing (4 px base) — and radii, motion
```css
--sp-1:  0.25rem;  --sp-2:  0.5rem;   --sp-3:  0.75rem;  --sp-4:  1rem;
--sp-6:  1.5rem;   --sp-8:  2rem;     --sp-12: 3rem;     --sp-16: 4rem;
--sp-24: 6rem;     --sp-32: 8rem;

--r-sm: 6px; --r-md: 10px; --r-lg: 16px; --r-pill: 999px;

--ease-out:  cubic-bezier(0.22, 1, 0.36, 1);
--dur-fast:  120ms;
--dur-base:  240ms;
--dur-slow:  480ms;
```

### 3.5 Layout
```css
--container-max:     1200px;        /* desktop content cap */
--container-narrow:  780px;         /* long-form prose, About, Insights */
--gutter:            clamp(1rem, 4vw, 2rem);
--breakpoint-sm:     480px;
--breakpoint-md:     768px;
--breakpoint-lg:     1024px;
--breakpoint-xl:     1440px;
```

### 3.6 Motion grammar
Sparingly used, per the brief. Section reveals only: opacity 0→1 + translateY 8px → 0 over `--dur-base` with `--ease-out`. No parallax. No scroll-jacking. `prefers-reduced-motion: reduce` disables all transitions unconditionally — implementation note for Turn 6.

### 3.7 Iconography
24-icon set, outlined, 1.5 px stroke, square-cap, no decorative flourish. Triangle (▲) for Market, diamond (◇) for Digital — preserved exactly from existing site. Build as a single inline-SVG sprite (`/assets/icons/sprite.svg`) referenced via `<use href="#icon-name">` for cache efficiency.

---

## 4 · Schema map (JSON-LD blocks per page type)

Every page carries `Organization` and `WebSite` schema in the global `<head>` — the rest is page-specific. All schema validated against Google Rich Results Test before launch (per brief §3.6).

| Page type | JSON-LD blocks |
|---|---|
| **Homepage** (`/`) | `Organization`, `WebSite`, `ItemList` (the START HERE block surfaced as a Service catalog), `FAQPage` (homepage AI-search FAQ block) |
| **About** (`/about`) | `Organization`, `Person` (founder Chukwudi Kingsley Eze), `BreadcrumbList` |
| **Services hub** (`/services`) | `CollectionPage`, `BreadcrumbList`, `ItemList` (both pillars) |
| **Pillar hub** (`/services/market`, `/services/digital`) | `CollectionPage`, `BreadcrumbList`, `ItemList` (services within pillar) |
| **Service detail** (`/services/{pillar}/{slug}`) | `Service`, `BreadcrumbList`, `FAQPage`, `Offer` (engagement tiers — even price-on-request) |
| **Tools hub** (`/tools`) | `CollectionPage`, `BreadcrumbList`, `ItemList` |
| **Tool detail** (`/tools/{slug}`) | `Service` (the diagnostic-as-service), `BreadcrumbList`, `FAQPage`, `HowTo` (the 3-step "pick context → answer questions → get scorecard" flow) |
| **Insights hub** (`/insights`) | `Blog`, `BreadcrumbList` |
| **Insight article** (`/insights/{slug}`) | `Article`, `Person` (author), `BreadcrumbList`, optionally `FAQPage` if the article includes a Q&A block |
| **Case studies hub** (`/case-studies`) | `CollectionPage`, `BreadcrumbList` (lightweight at launch) |
| **Case study detail** (post-launch) | `Article`, `Organization` (the client, if disclosed), `BreadcrumbList` |
| **Contact** (`/contact`) | `ContactPage`, `Organization` (with `contactPoint` for phone, email, WhatsApp) |
| **Legal** (`/legal/{slug}`) | `WebPage`, `BreadcrumbList` |

**Site-wide Organization schema (template):**
```json
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "GSLEY Digital",
  "alternateName": "GSLEY",
  "url": "https://gsleydigital.com",
  "logo": "https://gsleydigital.com/assets/img/gsley-digital-wordmark.svg",
  "description": "A productised growth and AI studio for institutions and ambitious scale-ups. Predictable demand and AI-native systems. Engineered, not opinionated.",
  "slogan": "Engineered Precision",
  "founder": {
    "@type": "Person",
    "name": "Chukwudi Kingsley Eze",
    "jobTitle": "Founder",
    "sameAs": "https://www.linkedin.com/in/chukwudihex"
  },
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Lagos",
    "addressRegion": "Lagos",
    "addressCountry": "NG"
  },
  "contactPoint": [{
    "@type": "ContactPoint",
    "telephone": "+234-902-476-7079",
    "contactType": "customer service",
    "email": "brief@gsleydigital.com",
    "availableLanguage": ["English"],
    "areaServed": "Worldwide"
  }],
  "sameAs": [
    "https://www.linkedin.com/company/gsleydigital/",
    "https://web.facebook.com/gsleydigital/",
    "https://x.com/gsleydigital/",
    "https://www.instagram.com/gsleydigital/"
  ]
}
```

**Per-service Service schema template:**
```json
{
  "@context": "https://schema.org",
  "@type": "Service",
  "serviceType": "{Service name}",
  "name": "{Service name}",
  "description": "{One-sentence outcome statement}",
  "provider": { "@type": "Organization", "name": "GSLEY Digital" },
  "areaServed": ["Nigeria", "Worldwide"],
  "hasOfferCatalog": {
    "@type": "OfferCatalog",
    "name": "Engagement tiers",
    "itemListElement": [
      { "@type": "Offer", "name": "Toolkit", "description": "..." },
      { "@type": "Offer", "name": "Sprint", "description": "..." },
      { "@type": "Offer", "name": "Retainer", "description": "..." }
    ]
  }
}
```

---

## 5 · Internal-link plan (rules — implemented in every template)

These are the linking rules every page must obey. The frontend developer implements them as template-level requirements in Turns 6–11.

1. **Every service detail page links to:**
   - Its pillar hub (e.g., Demand Generation → `/services/market`).
   - Three sibling services within the same pillar (the "Related Capabilities" block in the brief §1.3.2 template).
   - Its matched diagnostic tool (the COMPANION TOOL block).
   - The Discovery Call CTA destination (Contact or a dedicated booking URL).

2. **Every tool detail page links to:**
   - The tools hub.
   - The primary service the tool feeds (e.g., Growth Diagnostic → `/services/market/demand-generation`).
   - The Discovery Call CTA destination.

3. **Every insight article links to:**
   - One service page (the topical primary).
   - One tool page (the topical companion).
   - Two sibling insights (when the archive has them — launch placeholder: empty block, no broken state).

4. **The homepage links to:**
   - Both pillar hubs (Architecture section).
   - The six front-line service pages (START HERE block).
   - The eight tool pages (Tools Gallery block).
   - The About page (Founder Note block).
   - The Contact page (closing CTA).

5. **The footer links to:** every service, every tool, About, Contact, Insights, Case Studies, all three legal pages, all four social profiles, the NAP block (name / address / phone), and the WhatsApp pill.

6. **Breadcrumbs render on every non-homepage URL** in the order: Home › {Pillar / Section} › {Page} — with `BreadcrumbList` schema.

---

## 6 · Metadata pattern (the template every page fills)

Per brief §1.6.2: `<title>` ≤ 60 chars, meta description ≤ 155 chars, OG and Twitter tags on every page. Drafted in full per URL at Turn 12; the pattern below is the standard.

```html
<title>{Page H1, trimmed} — GSLEY Digital</title>
<meta name="description" content="{≤155 chars, declarative, includes one primary keyword}">

<meta property="og:type" content="{website|article|profile}">
<meta property="og:title" content="{Page H1}">
<meta property="og:description" content="{meta description, can be identical or expanded}">
<meta property="og:url" content="https://gsleydigital.com/{path}">
<meta property="og:image" content="https://gsleydigital.com/assets/og/{slug}.png">
<meta property="og:site_name" content="GSLEY Digital">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{Page H1}">
<meta name="twitter:description" content="{meta description}">
<meta name="twitter:image" content="https://gsleydigital.com/assets/og/{slug}.png">

<link rel="canonical" href="https://gsleydigital.com/{path}">
```

**OG image spec:** 1200×630, generated per page from a templated SVG (page title + GSLEY wordmark + pillar accent stripe). Built as a small Node script in Turn 11; static files served from `/assets/og/`.

**Locked metadata for the three highest-traffic URLs** (the rest follow the same pattern):

| URL | Title | Description |
|---|---|---|
| `/` | GSLEY Digital — Engineered Precision | A productised growth and AI studio for institutions and ambitious scale-ups. Predictable demand and AI-native systems. Engineered, not opinionated. |
| `/about` | About GSLEY Digital — The Archetype System™ | A board-level commercial systems studio. Two pillars, one operating method, documented archetypes — never opinions. |
| `/tools/growth-diagnostic` | The Gsley Growth Diagnostic™ — Free Scorecard | Five questions. A scored read-out of where revenue, reputation, and capacity are leaking. Instant scorecard, free, four minutes. |

---

## 7 · `llms.txt` plan

A clean machine-readable manifest at `/llms.txt`, per the emerging convention. Lists the canonical URLs, the methodology name, the founder, and the contact. Final content drafted at Turn 11; the file structure is locked here:

```
# GSLEY Digital
A productised growth and AI studio for institutions and ambitious scale-ups.

## Methodology
The Archetype System™ — documented archetypes, never opinions.

## Pillars
- GSLEY Market: predictable demand, brand authority, commercial systems
- GSLEY Digital: AI-native operating systems, agents, workflow architecture

## Primary URLs
- Homepage: https://gsleydigital.com/
- Services: https://gsleydigital.com/services
- Tools: https://gsleydigital.com/tools
- About: https://gsleydigital.com/about
- Contact: https://gsleydigital.com/contact

## Founder
Chukwudi Kingsley Eze, MCIPR — https://www.linkedin.com/in/chukwudihex

## Contact
brief@gsleydigital.com · +234 902 476 7079 · Lagos, Nigeria
```

---

## 8 · Hand-off to Turn 6

Foundation locked. Turn 6 ships the homepage as a working high-fidelity wireframe — HTML + CSS using the tokens above, three breakpoints (375 / 768 / 1440), and final production copy for every section including the three new ones the brief introduced (START HERE / Tools Gallery, Proof Strip / Founder Note, FAQ for AI-Search). Methodology threaded through as **The Archetype System™**.

No founder decisions blocking. Proceeding.

---

*End of Phase 1 Foundation. Author: GSLEY studio team. PM sign-off: assumed under "make best decisions and proceed" mandate. Override window: any time before Turn 12 compile.*
