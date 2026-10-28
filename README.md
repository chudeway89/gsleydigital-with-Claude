# GSLEY Digital — website

The WordPress code that runs **gsleydigital.com**, plus the design reference and planning documents.

## Repository layout

| Path | What it is |
|---|---|
| `wp-content/themes/gsley-digital/` | The block theme (Full Site Editing): `theme.json` tokens, header/footer parts, templates, locked block patterns for every page. |
| `wp-content/plugins/gsley-diagnostics/` | Application logic: GSLEY Settings (site facts), the services/tools registry, the ten tool blocks, lead capture (`/api/contact`), Resend email, Paystack, the lead dashboard and the one-click page importer. |
| `wp-content/plugins/gsley-seo/` | JSON-LD schema, meta descriptions and Open Graph images, `sitemap.xml`, `robots.txt`, `llms.txt`, legacy redirects. |
| `redesign/` | The October 2026 design reference (`Homepage v2.html`, `Contact v2.html`, `Health v2.html`, `gsley.css`, `site.js`) and `fix-tasks.md`. |
| `deploy/` | Upload guide, static `robots.txt` / `sitemap.xml`, and `REDESIGN-CHANGES.md` (what changed and what needs the owner). |
| `*.html`, `phase1-*.md`, `research*.md` | Phase 1 design reference and planning documents. |

The theme holds content and design; the plugins hold logic. Changing one never requires changing the other.

## Deploying from Git

The live site runs on Namecheap EasyWP (no SSH or WP-CLI), so deployment is "build a zip from this
repository and upload it in WP-admin". Each zip is a folder copied straight from `wp-content/`.

1. **Build the zips** from a clean checkout of the branch you are deploying:

   ```sh
   cd wp-content/themes  && zip -r ../../gsley-digital.zip gsley-digital -x '*/.git*'
   cd ../plugins         && zip -r ../../gsley-diagnostics.zip gsley-diagnostics -x '*/.git*'
   zip -r ../../gsley-seo.zip gsley-seo -x '*/.git*'
   ```

2. **Upload** in WP-admin:
   - Theme: **Appearance → Themes → Add New → Upload Theme** → `gsley-digital.zip` → **Replace active with uploaded**.
   - Plugins: **Plugins → Add New → Upload Plugin** → `gsley-diagnostics.zip`, then `gsley-seo.zip` → **Replace current with uploaded**.
3. **GSLEY Leads → Setup → Import / update pages** — rebuilds every page from the theme's patterns
   (this overwrites edits made to those pages in the editor).
4. **Settings → Permalinks → Save Changes**, then **purge the EasyWP cache**.
5. If the homepage looks unchanged, the front-page template was customised in the Site Editor:
   **Appearance → Editor → Templates → Front Page → ⋮ → Reset**.

Never commit secrets. Paystack and Resend keys go in `wp-config.php` or **GSLEY Leads → Settings**, not in this repository.

See `deploy/README.md` for the full checklist and `deploy/REDESIGN-CHANGES.md` for what the October 2026 redesign changed.
