# 🚀 Deployment Guide - Fix Vite Manifest Issue

## ❌ Current Problem
Your Laravel application is showing this error:
```
Illuminate\Foundation\ViteManifestNotFoundException
Vite manifest not found at: /home/u546312304/domains/green-wolverine-495039.hostingersite.com/public_html/public/build/manifest.json
```

## ✅ Solution

### Option 1: Upload Built Assets (Recommended)

1. **Download the built assets from your local machine:**
   ```bash
   # Run this locally to build assets
   npm install
   npm run build
   ```

2. **Upload the entire `public/build/` directory to your server:**
   - Source: `public/build/` (from your local machine)
   - Destination: `/home/u546312304/domains/green-wolverine-495039.hostingersite.com/public_html/public/build/`

3. **Verify the upload:**
   - Check that `manifest.json` exists at: `public_html/public/build/manifest.json`
   - Check that the `assets/` directory exists with all JS/CSS files

### Option 2: Build on Server (If you have Node.js access)

1. **SSH into your server and navigate to your project:**
   ```bash
   cd /home/u546312304/domains/green-wolverine-495039.hostingersite.com/public_html/
   ```

2. **Install dependencies and build:**
   ```bash
   npm install
   npm run build
   ```

3. **Copy manifest to correct location (if needed):**
   ```bash
   cp public/build/.vite/manifest.json public/build/manifest.json
   ```

### Option 3: Quick Fix Script

Upload the `server-diagnostic.php` file to your server and run it to get detailed information about what's missing.

## 🔧 After Uploading/Building

Run these commands on your server:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## 📁 Required Files Structure

Your server should have this structure:
```
public_html/
├── public/
│   ├── build/
│   │   ├── manifest.json          ← REQUIRED
│   │   ├── assets/                ← REQUIRED
│   │   │   ├── app-[hash].js
│   │   │   ├── app-[hash].css
│   │   │   └── [other files]
│   │   └── .vite/                 ← Optional
│   └── [other public files]
└── [other Laravel files]
```

## 🎯 Verification

1. **Check if manifest exists:**
   ```bash
   ls -la public_html/public/build/manifest.json
   ```

2. **Check manifest content:**
   ```bash
   head -5 public_html/public/build/manifest.json
   ```

3. **Check assets directory:**
   ```bash
   ls -la public_html/public/build/assets/
   ```

## 🚨 Common Issues

1. **Wrong path:** Make sure files go to `public_html/public/build/` not just `public_html/build/`
2. **Permissions:** Ensure files are readable by the web server
3. **Incomplete upload:** Make sure all files in the `build/` directory are uploaded
4. **Cache:** Clear Laravel caches after uploading

## 📞 Need Help?

If you're still having issues:

1. Upload `server-diagnostic.php` to your server
2. Run: `php server-diagnostic.php`
3. Share the output for further assistance

## 🔄 Alternative: Disable Vite Temporarily

If you need a quick fix while resolving the asset issue, you can temporarily disable Vite in your `app.blade.php`:

```php
<!-- Comment out these lines temporarily -->
{{-- @viteReactRefresh --}}
{{-- @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"]) --}}

<!-- Add fallback CSS/JS if needed -->
<link href="/build/assets/app-[hash].css" rel="stylesheet">
<script src="/build/assets/app-[hash].js" defer></script>
```

**Note:** Replace `[hash]` with the actual hash from your manifest.json file. 