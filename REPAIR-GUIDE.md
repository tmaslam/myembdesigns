# MyEMBDesigns.com - Resource Limit Repair Guide

## Problem Summary
Your live server has **corrupted plugin files** causing fatal PHP errors in an infinite loop.
This consumes all CPU/RAM resources, hitting your hosting limit.

## Root Causes
1. **WP Rocket** - Missing core class files → fatal error on every page load
2. **WooCommerce Action Scheduler** - Stuck jobs retrying forever
3. **Google Site Kit** - Missing required files
4. **38 active plugins** - Excessive memory usage
5. **WP_DEBUG enabled** - Writing massive logs constantly
6. **2GB uploads folder** - High disk usage

---

## Step-by-Step Fix

### STEP 1: Stop the Bleeding (Immediate)
Via cPanel File Manager or FTP on your LIVE server:

1. **Rename these folders** to disable broken plugins:
   ```
   wp-content/plugins/wp-rocket/      → wp-content/plugins/wp-rocket-BROKEN/
   wp-content/plugins/google-site-kit/ → wp-content/plugins/google-site-kit-BROKEN/
   ```

2. **Check if site loads now.** If yes, the fatal error loop is stopped.

---

### STEP 2: Re-Upload Fixed Plugin Files
From this local XAMPP copy, upload these folders to your live server:

**CRITICAL (re-upload these):**
- `wp-content/plugins/wp-rocket/`
- `wp-content/plugins/google-site-kit/`

**RECOMMENDED (if WooCommerce still has errors):**
- `wp-content/plugins/woocommerce/`

> Note: `js_composer/` and `revslider/` are premium plugins. If they're also broken,
> re-download from your ThemeForest/CodeCanyon account or re-upload from this local copy.

---

### STEP 3: Clear Stuck Action Scheduler Jobs

1. Upload `fix-action-scheduler.php` from this folder to your live server root (`public_html/`)
2. Visit: `https://www.myembdesigns.com/fix-action-scheduler.php`
3. **DELETE the file immediately after running** (security risk if left)

---

### STEP 4: Clean Up Uploads (Reduce Disk Usage)

Your uploads folder is **2GB**. Run this via SSH or ask your host:

```bash
# Find and remove oversized/unused files
cd wp-content/uploads/
# Remove backup zip if present
rm -f amazingcarousel-fix.zip
# Check for huge files
find . -type f -size +5M | head -20
```

Consider using an image optimization plugin like **Smush** or **ShortPixel** to compress images.

---

### STEP 5: Deactivate Unnecessary Plugins

You have **38 plugins**. Each plugin loads on every page, using memory.

**Safe to deactivate if you don't actively use them:**
- `ad-inserter` - Only if you don't run ads
- `cvw-social-share` - Redundant if using other social plugins
- `tawkto-live-chat` - Only if live chat is active
- `wc-captcha` - Only if using WooCommerce captcha
- `wp-social-icons` - Redundant
- `yith-woocommerce-zoom-magnifier` - Only if using zoom feature
- `product-catalog-feed` - Only if generating product feeds
- `custom-post-type-ui` - Safe after taxonomies are created
- `custom-taxonomy-creator` - Safe after taxonomies are created
- `list-custom-taxonomy-widget` - If not using custom taxonomy widgets
- `wp-custom-taxonomy-image` - If not using taxonomy images
- `all-in-one-wp-migration` - Safe to deactivate after migration

**Keep these (essential):**
- `woocommerce` + payment gateway
- `wordpress-seo` (Yoast)
- `contact-form-7` + `wpcf7-recaptcha`
- `wp-rocket` (once fixed)
- `js_composer` (if pages use it)
- `electro-extensions` (theme dependency)

---

### STEP 6: Verify wp-config.php Settings

Ensure your live `wp-config.php` has:

```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

---

## After Fixes - Monitor

1. Clear WP Rocket cache (once re-enabled)
2. Clear any CDN cache (Cloudflare, etc.)
3. Check `wp-content/debug.log` on live server - should stay empty
4. Monitor error logs for 24 hours

## Prevention

- **Never update plugins during high traffic**
- **Always backup before updates**
- **Keep plugin count under 20** if possible
- **Use a staging site** for updates
- **Set up automated backups** (UpdraftPlus free version)
