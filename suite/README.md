<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Outstanding / Pending — Xquisite Creations Suite

_Updated 2026-08-26. See `CLAUDE.md` for module/branch conventions._

**Shipped this section — merged to `dev` (PR [#70](https://github.com/Xoliswa00/Xquisite/pull/70), 2026-08-26):**
- Module-aware tenant dashboard: `DashboardController.php` and `dashboard.blade.php` rewritten so each tenant sees stat cards/revenue/activity only for modules they actually have active (Booking, POS, Property Management, E-commerce, Client Messaging) — previously every tenant saw a Booking/POS-shaped dashboard regardless of which modules they'd activated.
- Onboarding checklist rebuilt per-module instead of Booking-only steps; the Property checklist now walks property → unit → renter → lease in the order `leases.create` actually requires (it previously sent new landlords straight to a lease form that needs a unit and renter that didn't exist yet).
- Ran an 8-agent review (security/architecture/UX/performance/devil's-advocate/idea/feasibility/human-element) and fixed every confirmed finding: a stale-cache bug, redundant onboarding queries, a sidebar-priority comment/order mismatch, an unreachable mobile primary CTA, a missing POS revenue figure, and a Tailwind dynamic-class purge risk on order status badges.
- All of it verified against the live local DB (real tenants, not just Blade-compiles-clean) before merge.

**Still outstanding on `fix/property-management-bugs` (uncommitted, ~284 files as of this date):**
- The bulk of the branch's actual property-management work — inspection checklists, maintenance photo gallery, applicant screening, lease charges/deposits, and more — is still sitting uncommitted in the shared working tree, in progress across multiple concurrent sessions on this machine.
- An `Auditable` trait rollout across ~50 models is part of that uncommitted work. Its ownership was unclear across sessions as of 2026-08-25; **Xoliswa has since confirmed it's an intentional standing rule** (every Eloquent model must use `Auditable`; see `app/Models/Traits/Auditable.php` and project memory `feedback_auditable_mandatory.md`) — it's expected and wanted, just not yet committed/pushed.
- Recommend committing this work in scoped, reviewed chunks (as PR #70 was) rather than one large catch-all commit, given how many hands are in this working tree at once.

**Known bugs, not yet fixed:**
- `products.create` is gated behind `module:pos` middleware (`routes/web.php`), so a tenant with only the E-commerce module (no POS) cannot add products to their own store.

**Deferred (not urgent):**
- Dashboard Blade markup (revenue-anchor cards, stat tiles, activity lists) is duplicated ~3-5x across module sections. Fine at current module count; extract into `<x-dashboard.*>` components before a 6th module (analytics/loyalty/payroll are already seeded) gets its own section.

**Carried from `CLAUDE.md`:**
- Upload `/img/og-image.jpg` (1200×630) for WhatsApp/OG preview.
- Fix slug `Misstee-Beauty-Studio` → lowercase in DB.
- PayFast card payment integration (deferred).
- Post-appointment review request feature (not yet built).

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
