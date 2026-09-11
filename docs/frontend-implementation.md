# OUD frontend implementation

## Source of truth

Maintain the integrated Laravel application directly. Screens live in `resources/views/`, styles/images/JavaScript in `public/oud/`, translations in `lang/`, and workflows in `app/` and `routes/`. No separate design-folder synchronization step is required.

The 120 English/Arabic monthly report resources in `resources/reports/` retain the complete report main content, charts, source tables, notes and labelled demo profit/loss. `App\Support\ReferenceReports` reads these application-owned resources for rendering and initial financial-data seeding. Include them in deployments. The shared Laravel layout supplies authentication, navigation, language switching and role guidance; report links are rewritten to authorized application routes.

## Screen mapping

Blade paths below are relative to `resources/views/`.

| Screen | Route | Implementation |
| --- | --- | --- |
| Authentication | `/login`, `/sign-up`, `/forgot-password`, `/reset-password/{token}` | `auth/`, Laravel authentication controllers |
| Staff dashboards | `/dashboard/employee`, `/dashboard/department-manager` | `workspace/dashboard.blade.php` |
| Staff content | `/staff/documents`, `/staff/training`, `/staff/announcements`, `/staff/search` | Corresponding `workspace/` views |
| Landlord dashboard | `/dashboard/landlord` | `workspace/landlord-dashboard.blade.php` |
| Landlord lists | `/landlord/properties`, `/landlord/reports`, `/landlord/documents`, `/landlord/approvals` | Corresponding `workspace/` views and shared listing |
| Report/approval details | `/workspace/items/{id}` | `workspace/detail.blade.php` |
| Monthly financial report | `/landlord/properties/{id}/financials?year=2027&month=1` | `workspace/reference-financial-report.blade.php` or `workspace/financial-report.blade.php` |
| Admin financial preview | `/admin/properties/{id}/financials` | Same financial views, including authorized draft previews |
| Content management | `/content`, `/content/create` | Role-scoped content forms and controllers |
| Account/assignment editing | `/accounts/{user\|department\|property}/{id}/edit` | Admin account management |

The shared shell is `layouts/workspace.blade.php`. Lists support applicable property, search, category, period and approval-status filters, sorting, reset and pagination. Report details include sections and downloads. Approval details include request metadata, supporting files and persisted decisions.

## Data and workflows

Run `php artisan migrate` when deploying schema changes. Relevant migrations:

- `2026_09_10_120000_create_workspace_tables.php`: departments, user department assignment, properties, landlord assignments and typed workspace content.
- `2026_09_10_180000_add_financial_reporting_to_workspace_items.php`: nullable report month, financial data and unique property/month index.

All models use Laravel's configured database connection. Existing accounts, records and assignments must be preserved.

`php artisan oud:import-reference-content` imports five properties, 60 monthly financial records, six general reports, three documents and six approval requests. It assigns these properties to existing landlords without detaching other assignments or changing credentials. Repeat imports preserve existing records, uploaded files and final approval decisions. Normal `db:seed` also invokes this importer.

The dashboard shows assigned-property summaries. Imported reports retain their bundled EN/AR content. Editing an imported financial report through **Manage content** removes its reference marker and uses the editable database-backed financial view. Administrators can create additional monthly or general reports, set publication status and attach files. Duplicate property/month records are rejected.

Financial components include office, mezzanine, lobby/corridors, terrace, retail and outdoor. Blank values remain missing; explicit zeros are retained. Calculations sum component rents and service charges once. Collection rate requires positive rent due. Source tables, cell references, annual summaries and discrepancies are retained independently of calculations. Forecast revenue is not represented as net profit; demo P&L remains explicitly labelled.

## Access and persistence

- Dashboards require the matching role.
- Staff see published general or department-assigned staff content; drafts and future publications are hidden.
- Department managers require a department assignment and can manage only their department's supported content.
- Landlords see only published landlord content for assigned properties. Filters, financial routes, details, downloads and decisions recheck access.
- Administrators manage content, accounts, departments and property assignments.
- Public registration permits employee and landlord roles only.
- Approval decisions record reviewer, timestamp and optional comment transactionally. Repeated decisions return HTTP 409; decided requests cannot be edited.
- Uploads use the private local disk under `workspace/`; `/workspace/items/{id}/download` rechecks authorization. A public storage link is not required.

## Styles and interactions

Maintain the five shared stylesheets, `application.css`, `application.js`, `password-eye.js` and images directly in `public/oud/`. Financial styles are enabled only on financial pages. Application CSS includes responsive, RTL and print adaptations.

Application JavaScript progressively enhances internal GET navigation while preserving the sidebar/topbar. Direct navigation remains the fallback for unavailable JavaScript, downloads and expired sessions. Role dialogs support Escape/backdrop dismissal and focus restoration. Password controls provide localized accessible labels.

For future changes, update the relevant integrated views/assets and server actions together, preserve role and assignment checks, and add migrations only for new persisted data. Check both locales, desktop/mobile layout, filters and saved workflows.

## Material limitations

- Original binary contracts, proposals and the source workbook were not supplied. Bundled text is exported into valid, clearly labelled PDF/XLSX previews, not represented as original attachments. Administrator-uploaded files are retained.
- Optima/Swissra font files are not bundled. Readable fallbacks are available; Poppins and Noto Sans Arabic are requested from Google Fonts.
- External/Odoo synchronization and notification delivery are not configured. Status pages do not claim these services are active.
- Custom permission overrides and additional financial submission/review stages described in role guidance are not implemented workflows.

## Verification and deployment

Local automated coverage includes authentication, roles, assignment isolation, publication, uploads/downloads, filters, saved decisions, repeated imports, financial calculations and all 120 property/month/locale report variants. Tests use an isolated SQLite database, not production data.

On 11 September 2026, after consolidating report resources into the application, all **40 tests / 2,058 assertions** passed. All 120 report main sections were checked for exact preservation; shared stylesheets and images remained unchanged. Laravel Pint, Blade compilation and diff whitespace checks passed locally.

Before deployment, run:

```bash
php artisan test
php artisan view:cache
git diff --check
```

Deploy application assets and `resources/reports/` with the code. Run migrations in the target environment; run the importer or the existing seeding deployment command only when initial content is needed. Keep local `.env` settings separate from Laravel Cloud configuration. Local test and browser results are not production verification.
