<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## OUD frontend design reference

The root-level `OUD_project/` folder contains the frontend screens supplied by the frontend developer and is the design reference for this application. When implementing or updating screens for each role, adopt the corresponding designs, layouts, styling, assets, and interactions from this folder, while integrating the application's real data, workflows, and role permissions.

This folder may be updated or replaced with newer designs. When the project owner announces an update and requests frontend changes, review the latest contents and apply the relevant changes to the application's role-specific screens. Treat the folder as the supplied design reference; implement application changes in the appropriate application files.

## Frontend implementation

The supplied OUD designs are now integrated into Laravel authentication, staff and landlord workspaces, and the shared admin layout. Routes, database-backed content, private downloads, department/property assignments, and landlord decisions are implemented.

See [the implementation documentation](docs/frontend-implementation.md) for the screen map, database changes, access rules, verification, current limits, and the process for adopting future design updates.

```bash
php artisan migrate
php artisan oud:sync-design-assets
php artisan test
```

Use an admin account to create departments/properties and assign existing users, then publish documents, training, announcements, reports, and approval requests through **Manage content**. Empty workspaces show real empty states until records are added.

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

`php artisan db:seed --force` seeds application roles, the demo landlord and employee accounts, and the reference landlord workspace data without Faker or user factories. It is idempotent and can be rerun safely. Set `DEMO_USER_PASSWORD` in Laravel Cloud to override the demo password.

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
