#!/usr/bin/env python3
"""
GSLEY OG image generator
-------------------------
Generates 1200x630 Open Graph / social-share images, one per page, in the
"Engineered Precision" system: ink-on-paper or teal band per pillar, GSLEY
wordmark, the pillar glyph, and the page title in the display serif.

Usage:
    pip install pillow --break-system-packages
    python generate-og-images.py            # generates all pages in MANIFEST
    python generate-og-images.py --sample    # just the homepage, for a quick check

Production note: drop Source Serif 4 (display) and Inter (body) .ttf files into
./fonts/ and the script will use them; otherwise it falls back to DejaVu so it
always runs. Output -> ./assets/og/<slug>.png
"""
import os, argparse
from PIL import Image, ImageDraw, ImageFont

# --- design tokens ---------------------------------------------------------
INK   = (15, 27, 45)      # #0F1B2D
PAPER = (250, 249, 245)   # #FAF9F5
TEAL  = (11, 79, 73)      # #0B4F49
ACCENT= (22, 184, 166)    # #16B8A6  (digital accent)
MUTE  = (170, 180, 194)   # muted label on dark bands
HEALTH= (90, 34, 25)      # #5A2219  (health band)
HEALTH_ACCENT = (242, 184, 174)  # #F2B8AE
W, H  = 1200, 630
PAD   = 80
FONT_DIR = os.environ.get("GSLEY_OG_FONTS", os.path.join(os.path.dirname(__file__), "fonts"))

# slug, title, pillar ("market" | "digital" | "health" | "neutral")
MANIFEST = [
    ("home",                          "Capability is common. Commercial outcomes are not.", "neutral"),
    ("about",                         "A commercial systems studio. Engineered, not opinionated.", "neutral"),
    ("contact",                       "Start a conversation.", "digital"),
    ("services",                      "Three pillars. One commercial system.", "neutral"),
    ("services-market",               "GSLEY Market — predictable demand, engineered.", "market"),
    ("services-digital",              "GSLEY Digital — AI-native operating systems.", "digital"),
    ("services-health",               "GSLEY Health — healthcare growth, engineered for trust.", "health"),
    ("tools",                         "Ten tools. One clear read-out each.", "neutral"),
    ("website-design",                "Website Design & Development", "market"),
    ("google-ads",                    "Google Ads", "market"),
    ("email-marketing",               "Email Marketing", "market"),
    ("social-media-marketing",        "Social Media Marketing & Management", "market"),
    ("content-development",           "Content Development", "market"),
    ("demand-generation",             "Demand Generation Systems", "market"),
    ("b2b-sales-enablement",          "B2B Sales Enablement & Commercialisation", "market"),
    ("strategic-partnerships",        "Strategic Partnerships", "market"),
    ("institutional-brand-reputation","Institutional Brand & Reputation", "market"),
    ("thought-leadership",            "Thought-Leadership Programmes", "market"),
    ("marketing-automation",          "Marketing Automation", "market"),
    ("ai-agent-deployment",           "AI Agent Deployment", "digital"),
    ("automation-and-revops",         "Automation & RevOps", "digital"),
    ("ai-adoption-programmes",        "AI Adoption Programmes", "digital"),
    ("digital-transformation",        "Digital Transformation", "digital"),
    ("data-infrastructure",           "Data Infrastructure", "digital"),
    ("revenue-optimisation",          "Revenue Optimisation", "digital"),
    ("pr-marketing-communications",   "Healthcare PR, Marketing & Communications", "health"),
    ("revenue-and-tariff-optimisation","Revenue & Tariff Optimisation", "health"),
    ("thought-leadership-health",     "Healthcare Thought Leadership", "health"),
    ("growth-diagnostic",             "The Gsley Growth Diagnostic\u2122", "neutral"),
    ("ai-adoption-assessment-organisation", "AI Adoption Assessment — Organisation", "neutral"),
    ("ai-adoption-assessment-individual",   "AI Adoption Assessment — Individual", "neutral"),
    ("brand-authority-audit",         "Brand Authority Audit", "neutral"),
    ("revenue-leakage-calculator",    "Revenue Leakage Calculator", "neutral"),
    ("marketing-maturity-scorecard",  "Marketing Maturity Scorecard", "neutral"),
    ("automation-opportunity-finder", "Automation Opportunity Finder", "neutral"),
    ("partnership-readiness-quiz",    "Partnership Readiness Quiz", "neutral"),
    ("social-media-audit",            "Social Media Audit", "neutral"),
    ("digital-presence-orm-audit",    "Digital Presence & ORM Audit", "neutral"),
]

def load_font(candidates, size):
    """Try brand fonts in ./fonts, then DejaVu fallbacks shipped with Pillow."""
    for name in candidates:
        p = os.path.join(FONT_DIR, name)
        if os.path.exists(p):
            return ImageFont.truetype(p, size)
    for fb in ("DejaVuSerif.ttf", "DejaVuSans.ttf"):
        try:
            return ImageFont.truetype(fb, size)
        except OSError:
            continue
    return ImageFont.load_default()

def palette(pillar):
    if pillar == "digital": return TEAL, PAPER, ACCENT, "\u25C7"   # ◇
    if pillar == "market":  return INK,  PAPER, ACCENT, "\u25B2"   # ▲
    if pillar == "health":  return HEALTH, PAPER, HEALTH_ACCENT, "\u271A"  # ✚
    return INK, PAPER, ACCENT, "G"

def wrap(draw, text, font, max_w):
    words, lines, cur = text.split(), [], ""
    for w in words:
        trial = (cur + " " + w).strip()
        if draw.textlength(trial, font=font) <= max_w:
            cur = trial
        else:
            if cur: lines.append(cur)
            cur = w
    if cur: lines.append(cur)
    return lines

def render(slug, title, pillar, outdir):
    bg, fg, accent, glyph = palette(pillar)
    img = Image.new("RGB", (W, H), bg)
    d = ImageDraw.Draw(img)

    serif = load_font(["SourceSerif4-Regular.ttf", "SourceSerif4.ttf"], 64)
    mono  = load_font(["JetBrainsMono-Regular.ttf"], 24)
    glyphf= load_font(["DejaVuSans.ttf"], 24)
    brand = load_font(["Inter-SemiBold.ttf", "Inter-Bold.ttf"], 30)

    # top hairline + section marker
    d.line([(PAD, 150), (PAD + 60, 150)], fill=accent, width=2)
    if pillar == "neutral":
        d.text((PAD + 76, 138), "GSLEY DIGITAL", font=mono, fill=MUTE)
    else:
        # glyph in a font that has it (DejaVu), label in the mono face
        d.text((PAD + 76, 137), glyph, font=glyphf, fill=accent)
        d.text((PAD + 110, 138), f"GSLEY \u00B7 {pillar.upper()}", font=mono, fill=MUTE)

    # title (display serif, wrapped, bottom-left)
    lines = wrap(d, title, serif, W - 2 * PAD)
    line_h = 78
    y = H - PAD - len(lines) * line_h - 70
    for ln in lines:
        d.text((PAD, y), ln, font=serif, fill=fg); y += line_h

    # accent rule above the wordmark
    d.line([(PAD, H - PAD - 50), (PAD + 48, H - PAD - 50)], fill=accent, width=3)
    d.text((PAD, H - PAD - 36), "GSLEY DIGITAL", font=brand, fill=fg)
    d.text((W - PAD - 220, H - PAD - 32), "gsleydigital.com", font=mono, fill=MUTE)

    os.makedirs(outdir, exist_ok=True)
    path = os.path.join(outdir, f"{slug}.png")
    img.save(path, "PNG")
    return path

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--sample", action="store_true", help="only render the homepage")
    ap.add_argument("--out", default=os.path.join(os.path.dirname(__file__), "assets", "og"))
    args = ap.parse_args()
    items = MANIFEST[:1] if args.sample else MANIFEST
    for slug, title, pillar in items:
        print("wrote", render(slug, title, pillar, args.out))

if __name__ == "__main__":
    main()
