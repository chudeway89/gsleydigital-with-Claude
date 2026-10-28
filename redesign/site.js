/* GSLEY Digital — site.js (v2)
   ONE place to change contact details, navigation, services and tools.
   Every page renders its header, footer, mobile menu, WhatsApp button and action bar from SITE below.
   WordPress mapping: SITE.contact/booking -> plugin "GSLEY Settings" page; nav/footer -> synced patterns;
   SITE.tools -> registered tool blocks (count is computed, never typed). */

window.SITE = {
  brand: { name: "GSLEY", sub: "Digital", tagline: "A productised growth and AI studio. Three pillars, one commercial system — engineered for ambitious institutions and healthcare brands worldwide." },
  contact: {
    email: "brief@gsleydigital.com",
    phone: "+234 902 476 7079",
    phoneHref: "+2349024767079",
    whatsapp: "2349024767079",
    whatsappText: "Hi GSLEY, I'd like to talk about a project.",
    location: "Lagos · Enugu, Nigeria · working worldwide",
    offices: ["Lagos", "Enugu"] /* add street addresses in WP for LocalBusiness schema — not shown until verified */
  },
  /* Paste a Cal.com / Calendly link here and every "Book a Discovery Call" opens the scheduler.
     Leave empty and those buttons go to the contact form instead — no dead links either way. */
  bookingUrl: "",
  socials: [
    { label: "LinkedIn", href: "https://www.linkedin.com/company/gsleydigital/" },
    { label: "X", href: "https://x.com/gsleydigital/" },
    { label: "Instagram", href: "https://www.instagram.com/gsleydigital/" },
    { label: "Facebook", href: "https://web.facebook.com/gsleydigital/" }
  ],
  nav: [
    { label: "Home", href: "/" },
    { label: "Services", href: "/services" },
    { label: "Tools", href: "/tools" },
    { label: "About", href: "/about" },
    { label: "Contact", href: "/contact" }
  ],
  services: {
    market: [
      { slug: "website-design", label: "Website Design & Development", short: "Website Design", front: true },
      { slug: "google-ads", label: "Google Ads", short: "Google Ads", front: true },
      { slug: "email-marketing", label: "Email Marketing", short: "Email Marketing", front: true },
      { slug: "social-media-marketing", label: "Social Media Marketing & Management", short: "Social Media", front: true },
      { slug: "content-development", label: "Content Development", short: "Content", front: true },
      { slug: "demand-generation", label: "Demand Generation Systems", short: "Demand Generation" },
      { slug: "b2b-sales-enablement", label: "B2B Sales Enablement & Commercialisation", short: "B2B Sales Enablement" },
      { slug: "strategic-partnerships", label: "Strategic Partnerships", short: "Strategic Partnerships" },
      { slug: "institutional-brand-and-reputation", label: "Institutional Brand & Reputation", short: "Brand & Reputation" },
      { slug: "thought-leadership-programmes", label: "Thought-Leadership Programmes", short: "Thought Leadership" },
      { slug: "marketing-automation", label: "Marketing Automation", short: "Marketing Automation" }
    ],
    digital: [
      { slug: "ai-agent-deployment", label: "AI Agent Deployment", short: "AI Agent Deployment", front: true },
      { slug: "automation-and-revops", label: "Automation & RevOps", short: "Automation & RevOps", front: true },
      { slug: "ai-adoption-programmes", label: "AI Adoption Programmes", short: "AI Adoption", front: true },
      { slug: "digital-transformation", label: "Digital Transformation", short: "Digital Transformation" },
      { slug: "data-infrastructure", label: "Data Infrastructure", short: "Data Infrastructure" },
      { slug: "revenue-optimisation", label: "Revenue Optimisation", short: "Revenue Optimisation" }
    ],
    health: [
      { slug: "pr-marketing-communications", label: "Healthcare PR, Marketing & Communications", short: "PR, Marketing & Comms", front: true },
      { slug: "revenue-and-tariff-optimisation", label: "Revenue & Tariff Optimisation", short: "Revenue & Tariffs", front: true },
      { slug: "thought-leadership", label: "Healthcare Thought Leadership", short: "Thought Leadership", front: true }
    ]
  },
  tools: [
    { slug: "growth-diagnostic", name: "The Gsley Growth Diagnostic™", time: "4 min", out: "Scored read-out", flagship: true },
    { slug: "ai-adoption-assessment-organisation", name: "AI Adoption Assessment — Organisation", time: "6 min", out: "Board-ready PDF" },
    { slug: "ai-adoption-assessment-individual", name: "AI Adoption Assessment — Individual", time: "4 min", out: "Scored read-out" },
    { slug: "brand-authority-audit", name: "Brand Authority Audit", time: "5 min", out: "Scored read-out" },
    { slug: "revenue-leakage-calculator", name: "Revenue Leakage Calculator", time: "3 min", out: "Figure + breakdown" },
    { slug: "marketing-maturity-scorecard", name: "Marketing Maturity Scorecard", time: "5 min", out: "Scored read-out" },
    { slug: "automation-opportunity-finder", name: "Automation Opportunity Finder", time: "4 min", out: "Mapped opportunities" },
    { slug: "partnership-readiness-quiz", name: "Partnership Readiness Quiz", time: "3 min", out: "Scored read-out" },
    { slug: "social-media-audit", name: "Social Media Audit", time: "Live check", out: "Free score · fix list $10", paid: true },
    { slug: "digital-presence-orm-audit", name: "Digital Presence & ORM Audit", time: "Live check", out: "Free score · full plan $20", paid: true }
  ],
  legal: [
    { label: "Privacy", href: "/legal/privacy" },
    { label: "Terms", href: "/legal/terms" },
    { label: "Cookies", href: "/legal/cookies" }
  ]
};

(function () {
  var S = window.SITE, d = document;
  d.documentElement.classList.remove("no-js");

  /* Preview only: map production URLs to the reference files in this folder. Remove in WordPress. */
  var PREVIEW = { "/": "Homepage v2.html", "/contact": "Contact v2.html", "/services/health": "Health v2.html" };
  function url(h) { return (location.protocol !== "https:" || location.hostname.indexOf("gsleydigital.com") === -1) && PREVIEW[h.split("?")[0].split("#")[0]] ? PREVIEW[h.split("?")[0].split("#")[0]] + (h.indexOf("?") > -1 ? h.slice(h.indexOf("?")) : "") + (h.indexOf("#") > -1 ? h.slice(h.indexOf("#")) : "") : h; }
  function esc(s) { return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;"); }
  var wa = "https://wa.me/" + S.contact.whatsapp + "?text=" + encodeURIComponent(S.contact.whatsappText);
  var bookHref = S.bookingUrl || url("/contact") + "#brief";
  var bookAttrs = S.bookingUrl ? ' target="_blank" rel="noopener"' : "";
  var here = d.body.getAttribute("data-page") || "/";
  var waIcon = '<svg viewBox="0 0 32 32" width="26" height="26" fill="currentColor" aria-hidden="true"><path d="M16.04 3C9.4 3 4 8.4 4 15.04c0 2.12.55 4.16 1.6 5.98L4 29l8.2-1.55a12 12 0 0 0 3.84.63h.01c6.63 0 12.03-5.4 12.03-12.04C28.08 8.4 22.68 3 16.04 3zm0 21.9c-1.2 0-2.39-.2-3.51-.6l-.25-.1-4.87.92.93-4.74-.16-.26a9.86 9.86 0 0 1-1.51-5.22c0-5.46 4.45-9.9 9.92-9.9 2.65 0 5.13 1.03 7 2.9a9.82 9.82 0 0 1 2.9 7c0 5.47-4.45 9.9-9.92 9.9zm5.44-7.4c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>';
  var brandHTML = '<a href="' + url("/") + '" class="brand" aria-label="GSLEY Digital home"><span class="brand__g">G</span><span>SLEY</span><span class="brand__digital">Digital</span></a>';
  function cur(h) { return h === here ? ' aria-current="page"' : ""; }

  /* HEADER + MOBILE MENU */
  var hdr = d.querySelector("[data-gsley-header]");
  if (hdr) {
    hdr.className = "site-header";
    hdr.innerHTML =
      '<div class="wrap site-header__inner">' + brandHTML +
      '<nav class="nav" aria-label="Primary">' + S.nav.map(function (n) { return '<a href="' + url(n.href) + '"' + cur(n.href) + ">" + n.label + "</a>"; }).join("") + "</nav>" +
      '<a href="' + bookHref + '"' + bookAttrs + ' class="btn btn--solid header-cta">Book a Discovery Call <span class="arrow">↗</span></a>' +
      '<button class="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mnav"><span></span><span></span><span></span></button></div>' +
      '<div class="mobile-nav" id="mnav" role="dialog" aria-modal="true" aria-label="Menu">' +
      S.nav.map(function (n) { return '<a class="mnav-link" href="' + url(n.href) + '"' + cur(n.href) + ">" + n.label + "</a>"; }).join("") +
      '<div class="mobile-nav__group"><span class="mono-tag">Free diagnostic</span><a href="' + url("/tools/growth-diagnostic") + '">Run the Growth Diagnostic ↗</a></div>' +
      '<div class="mobile-nav__ctas"><a href="' + bookHref + '"' + bookAttrs + ' class="btn btn--solid btn--block">Book a Discovery Call ↗</a>' +
      '<a href="' + wa + '" target="_blank" rel="noopener" class="btn btn--wa btn--block">' + waIcon + " Chat on WhatsApp</a></div></div>";
    var tg = hdr.querySelector(".nav-toggle"), mn = hdr.querySelector("#mnav");
    function setNav(open) {
      mn.classList.toggle("open", open); tg.setAttribute("aria-expanded", open);
      tg.setAttribute("aria-label", open ? "Close menu" : "Open menu"); d.body.classList.toggle("nav-open", open);
      if (open) { var f = mn.querySelector("a"); f && f.focus(); }
    }
    tg.addEventListener("click", function () { setNav(!mn.classList.contains("open")); });
    mn.addEventListener("click", function (e) { if (e.target.closest("a")) setNav(false); });
    d.addEventListener("keydown", function (e) { if (e.key === "Escape" && mn.classList.contains("open")) { setNav(false); tg.focus(); } });
    window.addEventListener("resize", function () { if (window.innerWidth >= 768) setNav(false); });
  }

  /* FOOTER */
  var ftr = d.querySelector("[data-gsley-footer]");
  if (ftr) {
    function col(title, dot, list, pillar) {
      return '<div class="footer-col"><h4>' + (dot ? '<span class="dot ' + dot + '"></span>' : "") + title + "</h4><ul>" +
        list.map(function (s) { return '<li><a href="' + url(pillar ? "/services/" + pillar + "/" + s.slug : s.href) + '">' + esc(pillar ? s.short : s.label) + "</a></li>"; }).join("") +
        (pillar ? '<li><a href="' + url("/services/" + pillar) + '">All ' + ({ market: "Market", digital: "Digital", health: "Health" })[pillar] + " services ↗</a></li>" : "") + "</ul></div>";
    }
    ftr.className = "site-footer";
    ftr.innerHTML = '<div class="wrap"><div class="footer-grid">' +
      '<div class="footer-brand">' + brandHTML + "<p>" + esc(S.brand.tagline) + "</p>" +
      '<div class="contact"><a href="mailto:' + S.contact.email + '">' + S.contact.email + '</a><a href="tel:' + S.contact.phoneHref + '">' + S.contact.phone + "</a><span>" + esc(S.contact.location) + "</span></div>" +
      '<div class="socials">' + S.socials.map(function (s) { return '<a href="' + s.href + '" target="_blank" rel="noopener">' + s.label + "</a>"; }).join("") + "</div></div>" +
      col("GSLEY Market", "dot--m", S.services.market.filter(function (s) { return s.front; }), "market") +
      col("GSLEY Digital", "dot--d", S.services.digital.filter(function (s) { return s.front; }), "digital") +
      col("GSLEY Health", "dot--h", S.services.health.filter(function (s) { return s.front; }), "health") +
      col("Navigate", "", [{ label: "Tools", href: "/tools" }, { label: "About", href: "/about" }, { label: "Contact", href: "/contact" }]) +
      '</div><div class="footer-bottom"><span>© ' + new Date().getFullYear() + " GSLEY Digital · All rights reserved</span><nav aria-label=\"Legal\">" +
      S.legal.map(function (l) { return '<a href="' + url(l.href) + '">' + l.label + "</a>"; }).join("") + "</nav></div></div>";
  }

  /* WHATSAPP (desktop) + ACTION BAR (mobile) */
  if (!d.body.hasAttribute("data-no-fab")) {
    var fab = d.createElement("a");
    fab.className = "wa-fab"; fab.href = wa; fab.target = "_blank"; fab.rel = "noopener";
    fab.setAttribute("aria-label", "Chat with GSLEY on WhatsApp"); fab.innerHTML = waIcon; d.body.appendChild(fab);
    var bar = d.createElement("div");
    bar.className = "action-bar"; bar.setAttribute("aria-label", "Quick actions");
    var primary = d.body.getAttribute("data-bar-primary") === "book"
      ? '<a href="' + bookHref + '"' + bookAttrs + ' class="btn btn--solid">Book a Discovery Call ↗</a>'
      : '<a href="' + url("/tools/growth-diagnostic") + '" class="btn btn--solid">Run the Growth Diagnostic ↗</a>';
    bar.innerHTML = primary + '<a href="' + wa + '" target="_blank" rel="noopener" class="btn btn--wa" aria-label="Chat on WhatsApp">' + waIcon + "</a>";
    d.body.appendChild(bar); d.body.classList.add("has-action-bar");
    var shown = false;
    window.addEventListener("scroll", function () { var s = window.scrollY > 480; if (s !== shown) { shown = s; bar.classList.toggle("show", s); } }, { passive: true });
  }

  /* DATA BINDINGS: counts and contact values are computed, never typed into copy */
  var free = S.tools.filter(function (t) { return !t.paid; }).length;
  var words = ["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten", "eleven", "twelve"];
  function word(n, cap) { var w = words[n] || String(n); return cap ? w.charAt(0).toUpperCase() + w.slice(1) : w; }
  d.querySelectorAll("[data-count]").forEach(function (el) {
    var k = el.getAttribute("data-count"), n = k === "tools-free" ? free : k === "tools-free-others" ? free - 1 : k === "tools-all" ? S.tools.length : 0;
    el.textContent = word(n, el.hasAttribute("data-cap"));
  });
  d.querySelectorAll("[data-bind]").forEach(function (el) {
    var k = el.getAttribute("data-bind");
    if (k === "email") { el.textContent = S.contact.email; if (el.tagName === "A") el.href = "mailto:" + S.contact.email; }
    if (k === "phone") { el.textContent = S.contact.phone; if (el.tagName === "A") el.href = "tel:" + S.contact.phoneHref; }
    if (k === "location") el.textContent = S.contact.location;
    if (k === "whatsapp" && el.tagName === "A") { el.href = wa; el.target = "_blank"; el.rel = "noopener"; }
    if (k === "book" && el.tagName === "A") { el.href = bookHref; if (S.bookingUrl) { el.target = "_blank"; el.rel = "noopener"; } }
  });
  d.querySelectorAll("a[href^='/']").forEach(function (a) { var h = a.getAttribute("href"); var u = url(h); if (u !== h) a.setAttribute("href", u); });

  /* REVEAL — anything already in the first viewport shows immediately; the rest fades in on scroll */
  var rv = d.querySelectorAll(".reveal"), vh = window.innerHeight || 800;
  rv.forEach(function (el) { if (el.getBoundingClientRect().top < vh) el.classList.add("in"); });
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } }); }, { threshold: .1, rootMargin: "0px 0px -40px 0px" });
    rv.forEach(function (el) { if (!el.classList.contains("in")) io.observe(el); });
  } else rv.forEach(function (el) { el.classList.add("in"); });
})();
