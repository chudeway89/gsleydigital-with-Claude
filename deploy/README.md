# Deploy files — GSLEY Digital

## Files to upload to the web root
Upload these two files to the **same folder as `wp-config.php`** (EasyWP: SFTP → the site root),
replacing any existing copies:

| File | Purpose |
|---|---|
| `robots.txt` | Lets search engines and AI assistants (GPTBot, ClaudeBot, PerplexityBot, Google-Extended…) crawl the site; blocks admin/cart/search noise; points to the sitemap. |
| `sitemap.xml` | All 42 public pages (including the four GSLEY Health pages) with their canonical trailing-slash URLs. |

The SEO plugin serves identical versions automatically, so the site stays correct even if these
files are removed later. If you add pages, the plugin's `/sitemap.xml` updates itself — delete the
static `sitemap.xml` at that point, or regenerate it.

After uploading, submit `https://gsleydigital.com/sitemap.xml` in Google Search Console
(Sitemaps → Add). You can leave Yoast's `sitemap_index.xml` submitted too.

## Re-deploy the theme and plugins (October 2026 redesign)

The three zips are built from this repository (`wp-content/themes/gsley-digital`,
`wp-content/plugins/gsley-diagnostics`, `wp-content/plugins/gsley-seo`). See the root `README.md`
for how to rebuild them.

1. **Back up first** (EasyWP dashboard → Backups → Create backup).
2. **Plugins → Add New → Upload Plugin** → `gsley-diagnostics.zip` → **Replace current with uploaded**.
   Do the same with `gsley-seo.zip` and `gsley-security.zip`, and activate **GSLEY Security**.
   (Plugins first: the theme reads site facts and counts from them.) Then follow `SECURITY.md` §3:
   set up two-factor on your profile and save the backup codes.
3. **Appearance → Themes → Add New → Upload Theme** → `gsley-digital.zip` → **Replace active with uploaded**.
4. **GSLEY Leads → Setup → Import / update pages** — rebuilds all 42 pages and creates the four new
   GSLEY Health pages. (This overwrites manual edits made to those pages in the editor.)
5. **GSLEY Leads → GSLEY Settings** — check email, phone, WhatsApp and socials; add the booking link
   and verified office addresses when you have them.
6. **Settings → Permalinks → Save Changes**, then **purge the EasyWP cache**.
7. If the homepage still shows the old sections, the front-page template was customised in the
   Site Editor: **Appearance → Editor → Templates → Front Page → ⋮ → Reset**. Do the same for the
   Header and Footer template parts if they look old.
8. Upload the new `sitemap.xml` and `robots.txt` from this folder to the site root (replace the old ones).

### WP-admin settings to change by hand (fix-tasks.md, "Before Claude Code")
- **Settings → General:** Site Title `GSLEY Digital`, Tagline `Growth & AI Studio`.
- **Yoast → Settings:** the plugin now sets page titles, descriptions, og:type `website` and removes
  "Est. reading time" on pages automatically. A description you type into a page's Yoast panel wins.
- **Plugins:** deactivate TranslatePress (single language). Its floating switcher is hidden on mobile either way.
- **Media:** upload `redesign/assets/chukwudi-eze.jpg` when you have it (not placed on any page).

## Paystack keys (never commit keys to this repository)
Add to `wp-config.php`, above `/* That's all, stop editing! */`:

```php
define( 'GSLEY_PAYSTACK_SECRET_KEY', 'sk_live_…' );
define( 'GSLEY_PAYSTACK_PUBLIC_KEY', 'pk_live_…' );
```

Or paste them under **GSLEY Leads → Settings**. Then in the Paystack dashboard
(Settings → API Keys & Webhooks) set the live webhook URL to:

```
https://gsleydigital.com/wp-json/gsley/v1/pay/webhook
```

Payment currency defaults to **USD**; USD settlement must be enabled on the Paystack account,
otherwise set the currency to `NGN` in Settings and adjust the prices.

## Optional clean-up
- **TranslatePress:** only English is active, so the floating language switcher has no purpose.
  Turn it off under Settings → TranslatePress → Language Switcher → uncheck "Floating language selection".
- **WooCommerce:** nothing on the site sells through it (audits use Paystack). The theme no
  longer loads its files on normal pages; deactivating it entirely would be faster still.
