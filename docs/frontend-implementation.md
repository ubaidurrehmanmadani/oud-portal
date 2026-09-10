# OUD frontend implementation

## Scope and status — 10 September 2026

The project owner authorized adopting the screens in `OUD_project/`, including routes, controllers, database integration, and role permissions. The supplied reference folder remains unchanged so the frontend developer can replace it later.

Completed steps:

1. Reviewed all 16 supplied HTML screens, their CSS, supporting JavaScript, and the existing application.
2. Added database-backed departments, property assignments, workspace content, report metrics, and approval decisions.
3. Adapted the four authentication screens while retaining Laravel authentication, validation, password reset, CSRF protection, and locale switching.
4. Implemented the staff dashboard, document library, academy, announcements, and search.
5. Implemented the landlord dashboard, property portfolio, reports, documents, approvals, and report/approval detail pages.
6. Applied the shared design to admin pages; replaced sample rows with database records and connected content/account management forms.
7. Verified authentication, access boundaries, saved actions, rendering, and the local MySQL migration; reviewed desktop and Arabic screenshots and checked mobile layouts.

No separate admin or department manager designs are supplied. These roles use the shared design language with navigation and actions appropriate to their responsibilities.

## Screen and route mapping

| Reference file in OUD_project | Application route | Implementation |
| --- | --- | --- |
| `user_login.html` | `/login` | `auth/login.blade.php`, existing session controller |
| `user_register.html` | `/sign-up` | `auth/register.blade.php`, registration controller |
| `user_forgot-password.html` | `/forgot-password` | `auth/forgot-password.blade.php`, reset-link controller |
| `user_reset-password.html` | `/reset-password/{token}` | `auth/reset-password.blade.php`, new-password controller |
| `user_dashboard.html` | `/dashboard/employee`, `/dashboard/department-manager` | `WorkspaceController`, `workspace/dashboard.blade.php` |
| `user_documents.html` | `/staff/documents` | `workspace/documents.blade.php` |
| `user_training.html` | `/staff/training` | `workspace/training.blade.php` |
| `user_announcements.html` | `/staff/announcements` | `workspace/announcements.blade.php` |
| `user_search.html` | `/staff/search?q=...` | `workspace/search.blade.php` |
| `landlord_dashboard.html` | `/dashboard/landlord?property={id}` | `workspace/dashboard.blade.php` |
| `landlord_properties.html` | `/landlord/properties` | `workspace/properties.blade.php` |
| `landlord_reports.html` | `/landlord/reports` | `workspace/reports.blade.php` |
| `landlord_documents.html` | `/landlord/documents` | `workspace/documents.blade.php` |
| `landlord_approvals.html` | `/landlord/approvals` | `workspace/approvals.blade.php` |
| `landlord_report_detail.html` | `/workspace/items/{id}` for a report | `workspace/detail.blade.php` |
| `landlord_approval_detail.html` | `/workspace/items/{id}` for an approval | `workspace/detail.blade.php` |

The shared shell is `resources/views/layouts/workspace.blade.php`. Staff and landlord lists share `workspace/listing.blade.php`. Existing admin URLs and explicit core create-page views remain available. Admin module list pages support database search and pagination.

Additional working routes:

- `/content`: admin/manager content management, including drafts.
- `/content/create?kind=document|training|announcement|report|approval|user|department|property`: create forms, limited by role.
- `POST /content`, `PUT /content/{id}`: validated writes and file replacement.
- `/accounts/{user|department|property}/{id}/edit` and the corresponding `PUT` route: admin account and assignment updates.
- `/workspace/items/{id}/download`: authorized private download.
- `POST /workspace/items/{id}/decision`: landlord approval/rejection and optional comment, recorded transactionally with reviewer and timestamp.

## Database connection and migration

The configured local connection is the existing XAMPP MySQL database `oud`. Its connection settings and credentials were retained. The connection was verified and migration `2026_09_10_120000_create_workspace_tables.php` was applied successfully. Existing user records were preserved; no sample business records were inserted into MySQL.

New schema:

- `departments`: department names and descriptions.
- `users.department_id`: nullable assignment, allowing existing accounts to remain valid.
- `properties`: property name, location, type, unit count, status, and description.
- `property_user`: many-to-many landlord/property assignments.
- `workspace_items`: typed documents, training materials, announcements, reports, and approval requests. Includes audience, department/property scope, publication status/date, private file metadata, report metrics, and decision audit fields.

All models use Laravel's configured connection; there is no separate frontend connection. Deployments need to run `php artisan migrate`. Automated tests use an isolated in-memory SQLite database and do not alter local MySQL records.

Uploaded files are stored on Laravel's private `local` disk under `workspace/`. Downloads recheck authorization. A public storage link is not required. Supported uploads are limited to the configured document, image, video, archive, and text formats, up to 50 MB; PHP/web-server upload limits must also permit the intended file size.

## Access and content rules

- Each dashboard requires its matching role.
- Staff see published general staff content and published content assigned to their department. Drafts and future publications are hidden.
- Department managers need a department assignment to manage content. Their writes are restricted to that department's documents, training, and announcements; submitted foreign department/property IDs cannot expand their scope.
- Landlords see only landlord-audience records for assigned properties. Property filters, direct detail URLs, downloads, and decisions all enforce this scope.
- Admins can manage all workspace content, create/edit users and departments, and assign landlords to properties.
- Public registration offers employee and landlord roles. Admin and manager accounts must be created by an administrator.
- Approval decisions are final in this workflow. A repeated decision returns HTTP 409. Decided requests cannot be edited through content management.
- Static reference demo records, simulated login, and client-side decision scripts are not loaded into the application.

To populate an existing installation, use an admin account to create departments and properties, edit existing users to assign their departments/properties, then publish content through **Manage content**. Unassigned managers cannot publish; unassigned landlords see an empty portfolio.

## Design assets and future updates

`public/oud/styles.css` is copied from the supplied CSS. Reference imagery is copied to `public/oud/assets/`. Laravel-specific compatibility, responsive fixes, and font fallbacks live in `public/oud/application.css`. `public/oud/password-eye.js` supplies accessible localized password visibility controls. English and Arabic workspace labels live in `lang/en/workspace.php` and `lang/ar/workspace.php`.

Landlord list screens use one shared action convention: row content expands on the left and every action stays in a right-side cluster. View and Review use the dark primary pill, Download uses the outlined neutral variant, and Approve uses the olive success variant. This applies consistently to Properties, Reports, Documents, and Approvals and remains responsive by moving the action cluster below the row content on narrow screens.

Secure portal GET navigation is progressively enhanced in `public/oud/application.js`: internal screen links fetch the next rendered page and replace only `[data-page-content]`, update the page title and active sidebar link, and preserve the sidebar/topbar DOM. Direct navigation remains the fallback when JavaScript is unavailable, a download is requested, or the session expires.

When the owner supplies a new reference folder:

1. Compare the updated HTML, CSS, and prototype JavaScript with the integrated screens.
2. Run `php artisan oud:sync-design-assets` to copy the current CSS and assets. This preserves application-specific CSS/JS and does not import demo scripts.
3. Update Blade layouts, page structure, translations, and server actions as required by the revised design.
4. Add migrations only when new persisted data is needed; preserve existing records and assignments.
5. Run `php artisan test`, format changed PHP, and check desktop/mobile and both locales.

The source requests Optima/Poppins and Swissra typography, but does not include font files. Available system/web fonts are used with readable fallbacks. Poppins and Noto Sans Arabic are requested from Google Fonts.

## Deliberate limits

- The reference's invented KPI values and decorative trend chart are not treated as business data. The dashboard displays the latest published report's metrics and a real occupancy meter; missing figures display an empty state. Exact figures remain on report details; large dashboard revenue figures are abbreviated.
- Notification delivery and external/Odoo synchronization were not configured in the existing application. Their admin pages now show an unconfigured state instead of sample activity. No external integration or notification delivery is claimed.
- Runtime settings remain deployment/environment configuration; the settings screen shows actual non-secret values. Permissions display the stored roles/permissions, while application role and assignment checks govern the implemented workflow. Former placeholder setup URLs for these modules lead to their status pages.
- This is an integration of supplied frontend screens and their core workflows, not an implementation of unspecified external services, custom permission overrides, property photo management, or historical KPI ingestion.

## Verification

`php artisan test`: 25 tests and 440 assertions passing after the final changes. Tests cover authentication, EN/AR rendering, all dashboard role combinations, staff/landlord routes, cross-department/property isolation, private downloads, upload persistence, management forms, assignments, invalid input, registration privilege restrictions, and repeated approval decisions.

Headless Chrome screenshots use isolated test records, not production data. Desktop authentication/staff, Arabic landlord, and mobile views were reviewed. All 19 rendered pages passed a 390-pixel viewport check with document width equal to viewport width; navigation and wide document tables scroll within their own containers. The local MySQL migration was executed successfully without replacing the existing database.

Final checks: changed PHP formatted with Laravel Pint; Blade templates compiled successfully; `git diff --check` passed; `oud:sync-design-assets` ran successfully. The supplied `OUD_project/` files have no unstaged changes from this implementation.

## Production seeding correction

The Laravel Cloud production screenshot showed `Database\Factories\fake()` unavailable during `db:seed --force`. The default seeder used a user factory that depended on development-only Faker. The seeder now inserts missing application roles directly and creates no demo accounts. Existing accounts and customized roles remain unchanged on reruns. The fix was checked locally with Faker excluded from autoloading and an isolated SQLite database; it has not been deployed or verified against production. Local `.env` was unchanged. After deployment, rerun `php artisan db:seed --force` in Laravel Cloud production.
