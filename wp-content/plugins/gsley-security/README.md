# GSLEY Security (plugin)

Site-wide hardening for gsleydigital.com. Settings → **GSLEY Security**.

- **Headers:** nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy, COOP, HSTS, a
  baseline CSP that is always enforced, and a full CSP (report-only until switched on).
- **Login:** throttling and lockouts, one generic error message, user-enumeration routes closed,
  XML-RPC and application passwords off, WordPress version hidden.
- **Two-factor (TOTP)** for administrators (required by default), encrypted secrets, one-time
  backup codes. Emergency bypass: `define( 'GSLEY_SECURITY_DISABLE_2FA', true );` in wp-config.php.
- **Passwords:** 12+ characters and not in a known breach (Have I Been Pwned k-anonymity).
- **Sessions:** SameSite=Lax auth cookies, 8-hour admin sessions (3 days with "Remember me").
- **Log + alerts:** security events (kept 90 days, also written to the PHP error log), email alerts.
- `/.well-known/security.txt` for vulnerability reports.

See `SECURITY.md` at the repository root for the review, the owner checklist and the incident runbook.
