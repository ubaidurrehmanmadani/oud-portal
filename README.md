<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Application frontend

The integrated Laravel application is the source of truth for all future changes. Edit screens in `resources/views/`, styles and interactions in `public/oud/`, and application workflows in `app/` and `routes/`.

The 120 English/Arabic monthly report content resources live in `resources/reports/`. These application-owned files support existing imported reports and initial financial-data seeding; keep them in deployments.

## Frontend implementation

The supplied OUD designs are now integrated into Laravel authentication, staff and landlord workspaces, and the shared admin layout. Routes, database-backed content, private downloads, department/property assignments, and landlord decisions are implemented.

See [the implementation documentation](docs/frontend-implementation.md) for the screen map, database changes, access rules, verification, current limits, and maintenance guidance.

```bash
php artisan migrate
php artisan test
```

Use an admin account to create departments/properties and assign existing users, then publish documents, training, announcements, reports, and approval requests through **Manage content**. Empty workspaces show real empty states until records are added.

The landlord dashboard includes the supplied reference content for all five properties, including 60 monthly financial records, complete EN/AR report pages, source tables, charts and explicitly labelled demo profit/loss. Run `php artisan oud:import-reference-content` to populate the reference content and existing landlord assignments without resetting saved decisions or editing accounts. Normal `db:seed` also imports it. In **Manage content → Create report**, administrators can create additional reports or replace reference figures. Landlord access remains assignment-checked. See the implementation documentation for screen mappings and verification.

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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Production database seeding

`php artisan db:seed --force` seeds application roles, the four deployment accounts below, the existing demo landlord and employee accounts, and the reference landlord workspace data without Faker or user factories. It is idempotent and can be rerun safely. Set `DEMO_USER_PASSWORD` in Laravel Cloud to override the demo password.

If Laravel Cloud reports `Call to undefined function Database\Factories\fake()`, deploy the updated `database/seeders/DatabaseSeeder.php`, then rerun the command in the **production** environment:

```bash
php artisan db:seed --force
```

The demo credentials are `ubaid+landlord@gmail.com` and `ubaid+employee@gmail.com`, both using the seeded demo password. Local `.env` settings should not be copied into Laravel Cloud. Verification was performed against an isolated in-memory database with Faker unavailable; production still requires deployment and a successful command run.

## Laravel Cloud deployment commands

In the Laravel Cloud environment, open **Settings > Deployments > Deploy commands** and set:

```bash
php artisan migrate --force
php artisan db:seed --force
```

These commands run automatically before each deployment goes live. Run `php artisan optimize:clear` manually from the Cloud **Commands** tab when changing environment variables or troubleshooting stale configuration; it should not be part of every deploy because Cloud recommends preserving the deployment cache during releases.

## Deployment login accounts

Configure the server deployment hook to run `composer deploy` (or the two Laravel Cloud deploy commands above) after dependencies are installed. This runs migrations and seeding automatically on each deployment against the configured database. For SQLite, set `DB_CONNECTION=sqlite` and `DB_DATABASE` to an absolute SQLite file path on persistent, writable server storage. Keep that database across releases.

| Role | Email | Initial password |
| --- | --- | --- |
| Admin | admin@gmail.com | Test#12345 |
| Department Manager | manager@gmail.com | Test#12345 |
| Landlord | landlord@gmail.com | Test#12345 |
| Employee | employee@gmail.com | Test#12345 |

These accounts are created approved with hashed passwords and profiles. Existing accounts with these emails are preserved, including changed passwords and permissions. `DEMO_USER_PASSWORD` applies only to the older Ubaid demo accounts. The landlord receives the seeded property assignments; an Admin must assign the manager a department and any required reporting permissions through account management. Uploading files alone does not run seeders: the server deployment hook must be configured.

The login credentials card is temporarily enabled in production as well as locally. It shows the four deployment accounts above. To hide it later, set `DEMO_ACCESS_ENABLED=false` on the server and rebuild the configuration cache with `php artisan config:cache`. Keep `APP_ENV=production`.

## Backend delivery and user manuals

For a standalone walkthrough using the owner's existing departments, properties and corrected Manager login, see [the client testing guide](docs/client-testing-guide.md). Supply the client testing URL and passwords privately before sharing; its starting account snapshot describes the local environment.

For a complete role-by-role testing sequence, sample test records, expected results and the implemented-feature checklist, follow [the step-by-step acceptance testing guide](docs/testing-walkthrough.md). Start with Admin setup, then Manager publishing/reporting, Admin review and Employee/Landlord checks.

Latest continuation (16 September 2026): targeted announcements, Admin-managed Manager/Landlord permission overrides, delegated department employee management, visible-content previews, queue failure monitoring/retries and session revocation are implemented locally. The two 15 September migrations are applied to local MySQL. See the latest entry in [the backend delivery ledger](docs/backend-delivery.md) for verification and remaining work; Laravel Cloud deployment is not verified.

Backend work follows actual role order, beginning with administrator setup and access. Owner instructions, queue architecture, completed chunks, tests and remaining work are tracked in [the backend delivery ledger](docs/backend-delivery.md).

User guides are maintained in [English (PDF)](docs/manuals/user-manual-en.pdf) and [Arabic (PDF)](docs/manuals/user-manual-ar.pdf), with editable HTML sources and application HTML previews in `docs/manuals/`. They describe completed workflows only. Screen previews reuse Blade HTML at build time; no separate screenshot library is kept. Delivery follows the five-day plan in the backend ledger.

Password-reset email now requires a queue worker listening to `notifications`; configure the mail transport and supervise `php artisan queue:work --queue=uploads,notifications,default --timeout=60`. See the ledger for retries, deployment restart and monitoring requirements. Local test success does not verify mail delivery or Laravel Cloud operation.

### Open the manual inside the product

After login, select **User manual / دليل المستخدم** in the sidebar, or open `/help/manuals` on your application domain. Choose English or Arabic to open/download the PDF. Direct authenticated URLs are `/help/manuals/en.pdf` and `/help/manuals/ar.pdf`; append `?download=1` to download. Include both PDFs from `docs/manuals/` in deployments. This route serves the saved manual and does not generate images or PDFs per request.
