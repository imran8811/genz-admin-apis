# genz-admin-apis — Menu & Image Source of Truth (backend)

Backend API for **Gen Z Admin**. This is the **single source of truth for the menu**
(categories, items, deals, prices) and **images**, replacing the RMS in that role.
Consumed directly by [`genz-web`](../genz-web)/`genz-app` for display, and synced by
[`genz-web-apis`](../genz-web-apis) (checkout re-pricing) and [`genz-rms-apis`](../genz-rms-apis)
(costing). Admin UI is [`genz-admin`](../genz-admin) (Angular).

- **Stack:** Laravel 12, PHP 8.3, Laravel Sanctum (token auth), Intervention Image (GD).
- **DB:** MySQL `genz_admin_apis`. **Runs on:** `http://localhost:8002`.
- **Composer:** `C:\composer\composer.bat`. PHP `php` on PATH (winget).

## Run / setup
```bash
php artisan migrate:fresh --seed     # schema + admin user + import menu.json bootstrap
php artisan serve --port=8002        # API server
```
Seeded admin login: `admin@genzfoods.pk` / `password` (role `admin`).

## Data model
- `categories` (slug unique, type `single|sized`, sizes json, is_coming_soon, image_updated_at).
- `menu_items` (slug **unique + immutable**, price/prices json, flags, tag, `pizza_selection`,
  `deal_extras`, `default_size`, image_updated_at). **Deals** are menu_items inside a category
  whose slug ends in `deals` (carrying pizza_selection/deal_extras) — preserves the canonical
  feed shape.

## API surface
- Public: `GET /api/public/menu` — canonical menu feed (byte-compatible with the legacy RMS feed,
  **plus** an `image` URL per item/category). `GET /api/health`.
- `POST /api/auth/login` (admin only).
- Sanctum-protected `/api/admin/*`: `auth/me`, `auth/logout`; `apiResource` **categories** &
  **menu-items** (+ `/{slug}/image` upload, `/reorder`). `slug` is set once on create and never
  changes (it is the shared identity across all consumers).

## Images (the fixed-path contract)
On upload, `App\Services\ImageService` writes normalized webp to the **`menu` disk**
(`public/menu/`) at deterministic paths and bumps `image_updated_at`:
- `public/menu/{category}/{item}.webp` (display, ≤1000px) + `…-{item}-thumb.webp` (400×400).
Served at the clean URL `{ADMIN_PUBLIC_URL}/menu/{category}/{item}.webp`. The public feed emits
this URL with a cache-buster `?v={image_updated_at}` so re-uploads refresh everywhere.

## Key classes
- `app/Services/MenuFeed.php` — builds the public feed (canonical shape + image URLs).
- `app/Services/MenuImporter.php` — one-time bootstrap from `database/data/menu.json`.
- `app/Services/ImageService.php` — webp normalize + fixed-path storage.
- `app/Http/Controllers/Api/{PublicMenu,Category,MenuItem,Auth}Controller.php`.

## Conventions / gotchas
- **`slug` is immutable** — it ties orders, the web-apis price copy, and RMS recipe links together.
- Image processing needs the **GD extension with WebP** (enable `extension=gd` in php.ini).
- `php artisan serve` here uses a project-root `server.php` router (this Laravel install lacks the
  bundled one). **Avast may false-flag** `server.php`/`bootstrap/app.php` as webshells — exclude
  `E:\genz\genz-admin-apis` in Avast if files get quarantined.
- Format PHP with **Laravel Pint** before committing.
