# Security — gsleydigital.com

Review date: 28 October 2026. Scope: this repository (theme `gsley-digital`, plugins
`gsley-diagnostics`, `gsley-seo`, new `gsley-security`), plus a passive look at the live
site's public responses. No active testing was run against production.

**Report a vulnerability:** email the address in `https://gsleydigital.com/.well-known/security.txt`.

---

## 1. Findings and fixes

| # | Severity | Finding | Fix | Where |
|---|---|---|---|---|
| 1 | **Critical** | Live Paystack secret and public keys were pasted into a chat conversation. They are not in this repository, but they must be treated as exposed. | **Owner action:** roll the secret key in Paystack (Settings → API Keys & Webhooks → *Generate new secret key*) and put the new one in `wp-config.php`. | — |
| 2 | **High** | SSRF: the paid audit tools fetched any URL a visitor typed, following redirects — including internal addresses and the cloud metadata endpoint (`169.254.169.254`). | Outbound fetches allow only public http(s) hosts on ports 80/443; every redirect is re-checked; DNS is pinned to the validated IP (no rebinding); 1 MB / 10 s caps; transport errors are not echoed. | `gsley-diagnostics/includes/guard.php`, `audit.php` |
| 3 | **High** | Report emails trusted the browser: score, band, labels and name were taken from the request and emailed to any address, so the form could send arbitrary text/links from the GSLEY domain (phishing relay). | Results are recomputed on the server from the raw answers/inputs; unknown ids are dropped; names are stripped of links; email body escapes everything. Max 3 reports per recipient per hour, plus per-IP and site-wide caps. | `scoring.php`, `rest.php`, `report.php`, `guard.php` |
| 4 | **High** | No login throttling, and the admin username was public (`/wp-json/wp/v2/users`, `/?author=1`). | 5 failures per username+IP → 30-min lock; 20 per IP → 1-hour lock (checked after the password, so a correct password can't bypass it). One generic error message. User endpoints, author archives, oEmbed author fields and the users sitemap closed. | `gsley-security/includes/login.php` |
| 5 | **High** | No multi-factor authentication on WP-admin. | TOTP two-factor (any authenticator app), encrypted secret (AES-256-GCM), one-time hashed backup codes, replay protection; **required for administrators**. XML-RPC refused and application passwords disabled (both bypass the login form). | `gsley-security/includes/two-factor.php`, `login.php` |
| 6 | Medium | Payment verification checked the amount but not the currency; the webhook marked any matching reference as paid. | Exact amount **and** currency must match on confirm and webhook; the return URL is fixed server-side; Paystack errors are logged, not shown. | `audit-rest.php`, `paystack.php` |
| 7 | Medium | Settings page printed the stored Resend and Paystack secret keys into the page HTML. | Secrets are never echoed (shown as "set (ends …ab12)"); blank keeps the stored key; "clear" removes it. Changes are logged and alerted. | `gsley-diagnostics/includes/admin.php` |
| 8 | Medium | No Content-Security-Policy or Permissions-Policy; HSTS without `includeSubDomains`. | Baseline CSP enforced (frame-ancestors, base-uri, object-src, form-action, upgrade-insecure-requests); full CSP in Report-Only (one switch to enforce); Permissions-Policy, COOP, nosniff, Referrer-Policy, X-Frame-Options, HSTS 1 year (subdomains/preload switches). | `gsley-security/includes/headers.php` |
| 9 | Medium | Unbounded inputs: request bodies, field lengths, number of audited profiles. | Body caps (20–32 KB), field length caps, max 8 profiles per audit, numeric clamps. | `guard.php`, `contact.php`, `audit-rest.php` |
| 10 | Medium | CSV export allowed spreadsheet formula injection (`=HYPERLINK(...)` in a name field). | Cells starting with `= + - @` are neutralised. Exports are logged and alerted. | `admin.php` |
| 11 | Low | JSON-LD printed with unescaped `/`, so editor-entered text containing `</script>` could break out of the script tag. | `JSON_HEX_TAG | JSON_HEX_AMP`. | `render.php`, `gsley-seo/includes/schema.php` |
| 12 | Low | Personal data kept indefinitely with full IP addresses; no export/erase path. | IPs stored truncated and cleared after 30 days with browser strings; leads auto-deleted after a retention period (default 24 months); GSLEY data included in *Tools → Export/Erase Personal Data*; privacy-policy text suggested. | `privacy.php` |
| 13 | Low | WordPress auth cookies had no `SameSite`; admin sessions lasted up to 14 days. | `SameSite=Lax` added (HttpOnly/Secure already set by WordPress on HTTPS); admin sessions 8 h (3 days with "Remember me"); theme/plugin file editor disabled; WP version tag removed; PHP errors never displayed. | `login.php`, `gsley-security.php` |
| 14 | Info | No security logging or alerting. | Security event log (who/what/when/where, no secrets or form contents), 90-day retention, copy written to the server error log, email alerts for lockouts, new admins, 2FA changes, secret changes, exports, payment/webhook anomalies. | `gsley-security/includes/log.php` |

Checked and found sound: SQL (all queries prepared; only fixed table names interpolated),
REST object access (paid audits need a 24-character random token compared in constant time — no IDOR),
Paystack webhook HMAC-SHA512 verification, admin capability and nonce checks, output escaping in
admin screens and the tool JavaScript (including text scraped from audited sites).

## 2. How it was tested

- **SAST:** Semgrep (`p/php`, `p/javascript`, `p/owasp-top-ten`, `p/secrets`) — clean; manual review of every public endpoint.
- **DAST (local WordPress mirror, not production):** SSRF attempts (metadata IP, loopback, private ranges, IPv6, odd ports, a public URL redirecting to the metadata IP) all refused; tampered report payloads emailed only server-computed values; recipient limit returned 429 on the 4th request; login lockout held against the correct password; 2FA enrolment, wrong code, replayed code, backup code (single use) verified in a browser; `xmlrpc.php` 403; `/wp-json/wp/v2/users` 404; `?author=1` → home; headers present; no CSP violations on any page; all ten tools, Contact, payments-not-configured path regression-tested.
- **SCA:** the code has no Composer/npm dependencies. WordPress core and third-party plugins on the live site (WooCommerce 11.2.1, TranslatePress 3.3.8, Site Kit 1.190.0, Yoast) are outside this repository — keep auto-updates on (section 4).
- **CI:** `.github/workflows/security.yml` runs PHP lint (7.4 and 8.3), WordPress security sniffs, Semgrep and gitleaks on every push/PR and weekly; Dependabot keeps the actions current.

## 3. Deploy

Upload `gsley-security.zip` like the other plugins and activate it. Then, in this order:

1. **Settings → GSLEY Security** — read the Status list.
2. Your next visit to wp-admin takes you to **Profile → Two-factor authentication**: add the key to an authenticator app, enter the code, **save the 10 backup codes offline**.
3. Browse the site with the browser console open. If there are no "Content-Security-Policy" warnings, tick **Enforce the full Content-Security-Policy**. If something legitimate is blocked, add its host under *Extra allowed hosts*.
4. **Lost phone and backup codes:** add `define( 'GSLEY_SECURITY_DISABLE_2FA', true );` to `wp-config.php` over SFTP, sign in, reset 2FA on your profile, then remove the line.

## 4. Owner and host actions (cannot be done from code)

**Secrets**
- Roll the Paystack secret key (finding 1). Put Paystack and Resend keys in `wp-config.php` (`GSLEY_PAYSTACK_SECRET_KEY`, `GSLEY_RESEND_API_KEY`), not the settings screen; then type `clear` in the settings fields.
- Rotate WordPress salts once (`https://api.wordpress.org/secret-key/1.1/salt/` → `wp-config.php`). This signs everyone out. 2FA secrets are encrypted with these keys, so after rotating, administrators are asked to set up two-factor again at their next sign-in.
- Never paste keys into chat, email or tickets. If one leaks, roll it immediately.

**Accounts**
- Rename the public admin account (`chudeway89010` was exposed): create a new administrator with a non-obvious username, sign in as it, delete the old one (attribute content to the new user).
- One administrator per person; everyone else Editor or lower. Use a password manager; 16+ characters.

**Updates (SCA)**
- Dashboard → Updates: turn on auto-updates for WordPress core and every plugin. Remove plugins you don't use — WooCommerce and TranslatePress are not needed by the site today and are the largest attack surface.

**TLS / HSTS**
- EasyWP terminates TLS. Check `https://www.ssllabs.com/ssltest/analyze.html?d=gsleydigital.com` for grade A (TLS 1.2+ only). The host also sends HSTS (6 months); the plugin's 1-year header is sent too.
- Turn on HSTS *include subdomains* only after every subdomain serves valid HTTPS; *preload* only after that, then submit at hstspreload.org.

**WAF / DDoS / rate limiting at the edge**
- Put the domain behind Cloudflare (free plan is enough): proxy DNS, SSL mode *Full (strict)*, *Bot Fight Mode* on, Managed WAF rules on, and rate-limiting rules for `/wp-login.php` and `/wp-json/gsley/v1/*` (e.g. 20 requests / minute / IP). The plugin's limits are a second layer.

**Backups**
- EasyWP: enable automatic daily backups. Weekly, download a full backup (files + database) to separate storage with versioning/object lock (e.g. an S3 bucket with Object Lock, or encrypted Google Drive). Keep 30 days.
- Once a quarter, restore the latest backup to a staging site and check it loads. Write down how long it took.

**Environments**
- Use EasyWP staging (or a separate site) for testing — never the live database. If production data is copied to staging, delete or anonymise the GSLEY leads tables first.

**Logging / monitoring**
- Set the alert email in Settings → GSLEY Security. Add an uptime monitor (e.g. UptimeRobot) on `/` and `/wp-login.php`.
- Every security event is also written to the PHP error log (prefix `GSLEY-SECURITY`); if you adopt a log service, forward that log.

**Repository / pipeline**
- GitHub → Settings → Branches: protect `main` (require a pull request, 1 approving review, passing *Security checks*, no force-push). Turn on *Secret scanning* and *Push protection*. Enable 2FA on every GitHub account with access. Prefer signed commits.
- Deploys are manual zip uploads; only the owner's WP-admin account can install plugins.

## 5. Incident response (runbook)

1. **Contain (first hour).** Put the site in maintenance mode or, if defaced, restore the last clean backup. Change the passwords of all administrators; reset 2FA; roll the Paystack and Resend keys; rotate WordPress salts (signs everyone out).
2. **Preserve evidence.** Before cleaning, download a full backup of the compromised site, the server access/error logs (EasyWP dashboard) and *Settings → GSLEY Security* events. Note times in UTC. Don't edit files in place.
3. **Find the entry point.** Check the security log (new admins, logins from unknown IPs, plugin activations, secret changes), recently modified files, and unknown plugins/users. Compare plugin files with fresh copies from wordpress.org.
4. **Eradicate and recover.** Reinstall WordPress core and plugins from clean sources, redeploy the GSLEY theme/plugins from this repository, remove unknown users, update everything, then restore content if needed.
5. **Notify.** If personal data (leads, emails, phone numbers) may have been accessed: Nigeria Data Protection Act 2023 requires notifying the NDPC within 72 hours of becoming aware, and informing affected people where there is high risk; GDPR applies the same 72-hour rule for EU/UK individuals. Paystack: contact their support if payment flows were affected.
6. **Review.** Write down what happened, what was done, and what will change. Update this file.

Contacts to fill in: host (Namecheap EasyWP support), payment (Paystack support), email (Resend support), legal counsel, data-protection lead.
