# Xquisite

Xquisite is a multi-tenant business-management platform for service businesses. It brings bookings, point of sale, e-commerce, property management, client messaging, public websites, and platform billing into one application.

The main application lives in [`suite/`](suite/); the repository root also contains UI component prototypes used by the project.

## Technology

- PHP 8.2+ and Laravel 12
- Blade, Alpine.js, Tailwind CSS, and Vite
- SQLite for local development by default, with support for other database drivers through environment configuration

## Modules

- Bookings and customer management
- Point of sale, inventory, quotes, and payment plans
- Public e-commerce storefronts
- Property and renter management
- Tenant websites, branding, and analytics
- Platform billing and administration

## Getting started

The application setup, local development commands, test command, environment variables, and project structure are documented in the [Suite README](suite/README.md).

```bash
cd suite
composer setup
composer dev
```

Run the test suite from the same directory:

```bash
composer test
```

## Branches

- `dev` is the development branch.
- `main` is the production branch.

## License

This project is proprietary. See the [Suite README](suite/README.md) for details.
