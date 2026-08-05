# Xquisite Creations Suite

Multi-tenant business-management SaaS for South African service businesses — salons, gyms, restaurants, property managers, and event businesses run bookings, point-of-sale, e-commerce, property management, client messaging, and their own public website from one dashboard.

Built on Laravel 12, Blade, Alpine.js, and Tailwind CSS. Each tenant activates only the modules their business needs; the dashboard, navigation, and billing all key off that per-tenant module state.

## Modules

| Module | What it covers |
|---|---|
| **Bookings** | Appointments, calendar, customers, services (with categories, combos, promotions), staff |
| **POS** | Terminal (kiosk checkout), sales, quotes, products, rentals, suppliers, stock take, reorder alerts, purchase orders, layby/payment plans |
| **E-commerce** | Public storefront, cart, checkout (EFT / collection / PayFast), orders, store settings |
| **Property Management** | Properties, units, renters, leases, rent payments, maintenance requests, a renter self-service portal |
| **Client Messaging** | Internal client records, messaging, a client-facing portal |
| **Website** | A catalog of free public marketing-site templates a tenant can activate, a branding wizard (colors, fonts, logo, contact info), and per-tenant visit analytics |
| **Platform Billing** | Trial/grace/suspension lifecycle, invoices, proof-of-payment upload, plan management |
| **Admin (System Owner)** | Tenants, users, team members, plans, platform modules/services, website template catalog, reviews, sync queue, logs, monitoring, blocked IPs |

Public-facing surfaces (no login required): the storefront (`/shop/{tenant}`), the booking widget (`/book/{tenant}`), a per-tenant marketing website (subdomain or `/site/{tenant}`), the renter portal (`/rent/{tenant}`), and public quote views (`/q/{quote}`).

## Tech stack

- **Backend**: PHP 8.2+, Laravel 12, Spatie Permission (roles), Intervention Image, barryvdh/laravel-dompdf (invoices)
- **Frontend**: Blade, Alpine.js 3, Tailwind CSS 3 (`darkMode: 'class'`, CSS-variable token system for light/dark), Vite
- **Database**: SQLite by default in local dev (`DB_CONNECTION=sqlite`); swap to MySQL/Postgres in `.env` for other environments
- **Queue / cache / sessions**: database driver by default

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build      # or: npm run dev
```

Or run the bundled Composer script that does the above end-to-end:

```bash
composer setup
```

### Running the app

```bash
composer dev
```

This runs the PHP dev server, the queue listener, `pail` (log tailing), and the Vite dev server together via `concurrently`. Equivalent to running each of the following in its own terminal:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
php artisan pail --timeout=0
npm run dev
```

### Tests

```bash
composer test
```

## Environment notes

A few `.env` keys are specific to this app rather than stock Laravel:

| Key | Purpose |
|---|---|
| `APP_DOMAIN` | Base domain used to build each tenant's subdomain (`{subdomain}.{APP_DOMAIN}`) for storefronts and marketing sites |
| `BILLING_URL` / `BILLING_INTERNAL_KEY` | Internal billing-service integration |
| `PAYFAST_MERCHANT_ID` / `PAYFAST_MERCHANT_KEY` / `PAYFAST_PASSPHRASE` / `PAYFAST_SANDBOX` | PayFast checkout credentials (South African payment gateway) |

Multi-tenant resolution (subdomain and custom-domain lookup) happens in `ResolveTenant` middleware, backed by `App\Services\Tenant\TenantContext`.

## Project structure

```
app/
├── Http/Controllers/     # Admin, Auth, Booking, Ecommerce, POS, Property, Settings, Website, ...
├── Modules/               # Per-module domain code: Booking, Ecommerce, POS, Property
├── Models/                 # 36 Eloquent models — Tenant, TenantBranding, Template, TenantTemplate, ...
├── Services/Tenant/        # TenantContext, tenant resolution
resources/
├── views/
│   ├── layouts/app.blade.php        # Authenticated dashboard shell (sidebar, topbar, theming)
│   ├── layouts/guest.blade.php      # Auth pages shell
│   ├── components/                   # Shared Blade components (modal, dropdown, buttons, inputs, ...)
│   ├── site-templates/               # Public tenant-website templates (rendered via <x-site-layout>)
│   └── {bookings,pos,property,billing,admin,...}/   # One folder per module
├── css/app.css            # Tailwind + design-token definitions (light/dark CSS variables)
routes/web.php              # All route groups — see inline comments per module
```

## Design system

- Brand colors: primary blue `#0078D4`, gold accent `#D4AF37`, dark navy `#002B5B`.
- The dashboard supports light and dark mode (light by default; toggle in the sidebar footer), driven by CSS-variable tokens (`bg-app` / `bg-panel` / `bg-panel-2` / `text-ink` / `text-ink-muted` / `text-ink-faint` / `border-line` / `border-line-2`) defined in `resources/css/app.css` and registered in `tailwind.config.js`. As of this writing the shell, shared components, and a handful of pages are migrated to these tokens — most module views still use the legacy hardcoded slate palette pending a full pass.
- Public tenant marketing sites have their own, separate token set (`--site-*`) and their own light/dark toggle, independent of the dashboard's.
- See `CLAUDE.md` at the repo root for the full, current design-token reference and working conventions for this project.

## Branches

| Branch | Environment |
|---|---|
| `dev` | Development — all work happens here |
| `main` | Production — xquisite.brightfinance-x.co.za (cPanel, PHP 8.4, LiteSpeed) |

## License

Proprietary — all rights reserved. (`composer.json` still carries the default Laravel-skeleton MIT license field; that hasn't been updated to reflect this app's actual licensing and is worth revisiting separately from this README.)
