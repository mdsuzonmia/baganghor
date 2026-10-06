# Taharat Agro — Phase 1

## Requirements

- PHP 8.2 or newer with `intl`, `mbstring`, and `mysqli`
- MySQL 8+ or a current MariaDB release
- A web server whose document root points to `public/`

## Setup

1. Copy `.env.example` to `.env`.
2. Set `app.baseURL`, database connection values, and strong initial admin credentials.
3. Create the configured empty database.
4. Run:

```bash
composer install
php spark migrate
php spark db:seed DatabaseSeeder
```

Open `/admin/login` and immediately replace the seeded password from **My Profile**. The fallback development credentials are `admin@taharatagro.local` / `ChangeMe123!` only when environment values are absent; never use that fallback in production.

For local development, run `php spark serve`. This XAMPP installation includes a root `.htaccess` that maps `/taharat_agro` internally to `public/` and blocks direct access to source, dependencies, runtime data, and environment files. For production Apache/Nginx, pointing the virtual-host document root directly to `public/` remains the preferred deployment. Enable HTTPS in production and keep `app.forceGlobalSecureRequests = true`.

## Phase boundary

This release contains the database foundation, customer/admin authentication, role gates, settings, activity logs, and management shell. Commerce modules shown in the navigation are intentionally non-functional placeholders for later phases.
