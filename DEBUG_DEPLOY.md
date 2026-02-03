# Debugging deploy (no server/DB access)

Use the browser only. Report back the results so we can narrow down the issue.

## 1. Direct logo URL

Open this URL in a new tab (replace with your test domain if different):

```
https://test.honr.hands-on-technology.org/images/logos/programs/1.jpg
```

- **Image loads** → Files are deployed and served; issue is likely frontend (wrong URL) or API (wrong `logo_path` in response).
- **404 / Forbidden** → Files not at that path on server, or web server not serving `/images/` from backend `public/`.

## 2. Network tab (what the app requests)

1. Open the app (e.g. Awards page).
2. Open DevTools → **Network**.
3. Reload the page.
4. In the list, find requests that look like `/images/logos/...` (program/season/role logos).

For **one** such request note:

- **Request URL** (full URL).
- **Status** (200, 404, etc.).

If you don’t see any request to `/images/logos/...`, the app may be using a different URL (e.g. `/storage/...`); note one example URL for a logo.

## 3. What the API returns (`logo_path`)

1. In Network tab, find an XHR/fetch to the API (e.g. `/api/engagements` or `/api/...` that loads awards data).
2. Open it → **Response** (or Preview).
3. Find an object that has a `logo_path` (e.g. on a program, season, or role).

Note one example, e.g.:

- `"logo_path": "images/logos/programs/1.jpg"`  
- or `"logo_path": "images/logos/programs/default.svg"`  
- or `"logo_path": "logos/roles/1.svg"` (old path).

---

**What to send back**

- Result of step 1 (image loads vs 404/other).
- One logo **Request URL** and **Status** from step 2.
- One example **`logo_path`** from step 3.

With that we can tell whether the problem is: files not deployed, wrong web server path, wrong URL in frontend, or DB/API still returning old paths.

---

## When one logo works (e.g. 1.jpg) and another fails (e.g. 3.png)

1. **Test the failing file directly**  
   Open in a new tab:  
   `https://test.honr.hands-on-technology.org/images/logos/programs/3.png`  
   - Loads → file is there; the “error loading resource” may be MIME type or caching; we added `public/images/.htaccess` so `.png` is served as `image/png`.  
   - 404 / 403 / other → file missing or not readable on server; check deploy included `backend/public/images/logos/programs/3.png` and server docroot is `…/public`.

2. **Check what the API returns for that program**  
   In Network tab, open the API response that contains programs (e.g. engagements or programs list). Find the program whose logo fails (e.g. id 3) and note its `logo_path`.  
   - Should be like `images/logos/programs/3.png`.  
   - If it’s something else (e.g. `default.svg` or an old path), the repair migration may not have run after deploy, or the file wasn’t present when it ran; re-run migrations on the server or fix the path in DB/Admin.
