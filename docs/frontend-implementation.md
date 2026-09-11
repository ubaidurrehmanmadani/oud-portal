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
| Manager submissions | `/manager/reports` | `manager/reports.blade.php`, search, status filter and pagination |
| Manager upload/draft/detail | `/manager/reports/upload`, `/manager/reports/{id}/edit` | `manager/upload-report.blade.php` |

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
- Public registration offers all four roles. Admin and Department Manager applicants are saved as pending, remain logged out and cannot access protected routes until an existing approved Admin approves them. Employee and Landlord registration is unchanged.
- Approval decisions record reviewer, timestamp and optional comment transactionally. Repeated decisions return HTTP 409; decided requests cannot be edited.
- Uploads use the private local disk under `workspace/`; `/workspace/items/{id}/download` rechecks authorization. A public storage link is not required.

## Account approval — 11 September 2026

Migration `2026_09_11_150000_add_account_approval_to_users.php` adds approval status, reviewer and decision timestamp. Existing and Admin-created accounts default to approved. Public privileged registrations explicitly set pending before insertion; submitted approval/permission fields are ignored. Both login and web middleware enforce approval, including already-authenticated pending sessions.

Admins use **Account approval requests** (`/admin/users/account-requests`) to inspect applicants, edit department assignments and approve or reject access. Decisions are transactionally locked, reject self-approval and repeat decisions, and write audit records. Rejected accounts remain blocked. Approving a manager does not grant financial upload permission. Email notification delivery is not implemented; applicants return to login after approval. Local regression coverage: **48 tests / 2,179 assertions**.

## Manager workflow — 11 September 2026

- Department Managers have dashboard shortcuts to create documents, training and announcements, plus content management. Existing private department libraries, search, download, publication and replacement remain available. Content management now has search/type/status filters and a two-stage removal control; removal deletes the attachment and rechecks department scope.
- Run migration `2026_09_11_120000_create_manager_report_submissions.php`. It adds a default-off user permission, private `report_submissions` records and `audit_events` for content saves/removal and financial draft/submission actions. Authentication audit records remain separate.
- An Admin enables **Allow financial report submissions** on an existing user's edit screen, selects **Department Manager**, assigns a department, and selects reporting properties. Reserve this permission for the Head of Property Management and Head of Hospitality Management. It is not granted automatically to department managers or based on department names. No existing account permissions are changed by the migration.
- Authorized managers can upload XLSX/XLS/PDF files up to 20 MB, select an assigned property and month, add optional manual occupancy/revenue/rent figures and notes, save/replace a draft, download it privately and submit for review. One submission per manager/property/month prevents accidental duplicates. Pending submissions are locked; other managers, employees and landlords cannot open or download them. Revoking the permission, department or property assignment revokes access.
- Uploads are stored privately under `report-submissions/`. File type/extension/size and ownership checks run server-side. The submitted original, month, author, department, figures and timestamp are retained. Submissions are separate from published workspace reports, so uploads cannot change landlord dashboards.
- This step implements the manager-side submission workflow, not automatic Excel parsing or the Admin approval/publication screen. The UI explicitly states that spreadsheet figures are not extracted. Pending submissions remain private until the review/publication step is implemented. Template-to-cell mapping, reviewer corrections/rejection/resubmission and email delivery remain follow-up work; no notification delivery is claimed.
- Managers still cannot administer accounts, change system settings or view landlord records. The optional requirement for Admin-granted user-account administration is not implemented as part of the financial submission permission.
- Local automated verification: **45 tests / 2,123 assertions**, covering private upload/replacement, validation, duplicate months, permissions, assignment revocation, submission locking, department deletion and existing application regressions.
- The migration was applied to local MySQL without changing existing account permissions. Headless Chrome verified manager navigation, a real file upload, draft save, submission locking, and English/Arabic desktop/mobile layouts against an isolated SQLite database. Blade compilation and formatting passed. Laravel Cloud still requires deployment and migration; these are local checks only.

## Styles and interactions

Maintain the five shared stylesheets, `application.css`, `application.js`, `password-eye.js` and images directly in `public/oud/`. Financial styles are enabled only on financial pages. Application CSS includes responsive, RTL and print adaptations.

Application JavaScript progressively enhances internal GET navigation while preserving the sidebar/topbar. Direct navigation remains the fallback for unavailable JavaScript, downloads and expired sessions. Role dialogs support Escape/backdrop dismissal and focus restoration. Password controls provide localized accessible labels.

Property financial pages use the full available body width beside the sidebar, without a fixed desktop content-width cap. Responsive padding and internally scrolling tables are retained for both locales.
Property sidebar links use flex alignment to keep their labels vertically centered on desktop and mobile in both locales.
All authentication and workspace pages use the shared language-switcher partial: one joined pill with an olive active segment, cream background, bronze inactive label and desert outline. English stays on the left and Arabic on the right in both locales; keyboard focus and pressed state are exposed accessibly.

For future changes, update the relevant integrated views/assets and server actions together, preserve role and assignment checks, and add migrations only for new persisted data. Check both locales, desktop/mobile layout, filters and saved workflows.

## Material limitations

- Original binary contracts, proposals and the source workbook were not supplied. Bundled text is exported into valid, clearly labelled PDF/XLSX previews, not represented as original attachments. Administrator-uploaded files are retained.
- Optima/Swissra font files are not bundled. Readable fallbacks are available; Poppins and Noto Sans Arabic are requested from Google Fonts.
- External/Odoo synchronization and notification delivery are not configured. Status pages do not claim these services are active.
- General custom permission overrides and Admin financial review/publication are not implemented. The manager-specific financial submission grant is implemented as described above.

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
