# Deploy files — GSLEY Digital

## Files to upload to the web root
Upload these two files to the **same folder as `wp-config.php`** (EasyWP: SFTP → the site root),
replacing any existing copies:

| File | Purpose |
|---|---|
| `robots.txt` | Lets search engines and AI assistants (GPTBot, ClaudeBot, PerplexityBot, Google-Extended…) crawl the site; blocks admin/cart/search noise; points to the sitemap. |
| `sitemap.xml` | All 38 public pages with their canonical trailing-slash URLs. |

The SEO plugin serves identical versions automatically, so the site stays correct even if these
files are removed later. If you add pages, the plugin's `/sitemap.xml` updates itself — delete the
static `sitemap.xml` at that point, or regenerate it.

After uploading, submit `https://gsleydigital.com/sitemap.xml` in Google Search Console
(Sitemaps → Add). You can leave Yoast's `sitemap_index.xml` submitted too.

## Re-deploy the theme and plugins
1. Re-upload the `gsley-digital` theme, `gsley-diagnostics` plugin and `gsley-seo` plugin folders
   (Plugins → Add New → Upload → "Replace current with uploaded").
2. **GSLEY Leads → Setup → Import / update pages** — rewrites page content with the fixed links.
   (This overwrites manual edits made to those pages in the editor.)
3. **Settings → Permalinks → Save Changes.**
4. **Purge the EasyWP cache.**

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
