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
| `landlord_dashboard.html` | `/dashboard/landlord` | `workspace/landlord-dashboard.blade.php` |
| `landlord_properties.html` | `/landlord/properties` | `workspace/properties.blade.php` |
| `landlord_reports.html` | `/landlord/reports` | `workspace/reports.blade.php` |
| `landlord_documents.html` | `/landlord/documents` | `workspace/documents.blade.php` |
| `landlord_approvals.html` | `/landlord/approvals` | `workspace/approvals.blade.php` |
| `landlord_report_detail.html` | `/workspace/items/{id}` for a report | `workspace/detail.blade.php` |
| `landlord_approval_detail.html` | `/workspace/items/{id}` for an approval | `workspace/detail.blade.php` |
| `excel_report_{reserve,square,dunes,east,west}_{01..12}{,_ar}.html` | `/landlord/properties/{id}/financials?year=2027&month=1` | `workspace/financial-report.blade.php` and shared chart/table partials |

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

All five reference stylesheets (`styles.css`, `excel-reporting.css`, `report-sidebar.css`, `user-role-button.css`, `user-role-popup.css`) are synchronized into `public/oud/`. Reference imagery is copied to `public/oud/assets/`. Financial report styles are enabled only on financial pages, including when using progressive navigation. Laravel-specific compatibility, responsive fixes, and font fallbacks live in `public/oud/application.css`. `public/oud/password-eye.js` supplies accessible localized password visibility controls. English and Arabic labels live in the `workspace.php`, `financial.php` and `role-guide.php` translation files.

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

- The updated landlord dashboard shows summaries for assigned properties and links to monthly financial reports. Charts use saved, visible monthly records; missing figures remain unavailable rather than becoming invented values. Older reports retain their existing detail pages and downloadable attachments.
- Notification delivery and external/Odoo synchronization were not configured in the existing application. Their admin pages now show an unconfigured state instead of sample activity. No external integration or notification delivery is claimed.
- Runtime settings remain deployment/environment configuration; the settings screen shows actual non-secret values. Permissions display the stored roles/permissions, while application role and assignment checks govern the implemented workflow. Former placeholder setup URLs for these modules lead to their status pages.
- This is an integration of supplied frontend screens and their core workflows, not an implementation of unspecified external services, custom permission overrides, property photo management, or historical KPI ingestion.

## Verification

See the September reference-refresh verification below for the latest checks. The following records the original integration baseline.

`php artisan test`: 25 tests and 440 assertions passing after the final changes. Tests cover authentication, EN/AR rendering, all dashboard role combinations, staff/landlord routes, cross-department/property isolation, private downloads, upload persistence, management forms, assignments, invalid input, registration privilege restrictions, and repeated approval decisions.

Headless Chrome screenshots use isolated test records, not production data. Desktop authentication/staff, Arabic landlord, and mobile views were reviewed. All 19 rendered pages passed a 390-pixel viewport check with document width equal to viewport width; navigation and wide document tables scroll within their own containers. The local MySQL migration was executed successfully without replacing the existing database.

Final checks: changed PHP formatted with Laravel Pint; Blade templates compiled successfully; `git diff --check` passed; `oud:sync-design-assets` ran successfully. The supplied `OUD_project/` files have no unstaged changes from this implementation.

## Production seeding correction

The Laravel Cloud production screenshot showed `Database\Factories\fake()` unavailable during `db:seed --force`. The default seeder used a user factory that depended on development-only Faker. The seeder now inserts missing application roles directly and creates no demo accounts. Existing accounts and customized roles remain unchanged on reruns. The fix was checked locally with Faker excluded from autoloading and an isolated SQLite database; it has not been deployed or verified against production. Local `.env` was unchanged. After deployment, rerun `php artisan db:seed --force` in Laravel Cloud production.

### PHP 8.2 / Laravel 12 compatibility (2026-09-10)

Authentication models use `$fillable` and `$hidden` properties in place of unsupported Eloquent attributes after the Laravel downgrade. This restores registration, role/profile/login-event creation, and password/token hiding during serialization. No migration or `.env` change is required. Clear compiled views and configuration after downgrading (`php artisan view:clear` and `php artisan config:clear`) to remove stale exception templates.

Local verification uses `/usr/local/opt/php@8.2/bin/php` (8.2.29); the default `php` command currently runs 8.4.8. Authentication and workspace tests use an isolated SQLite database, not the local MySQL database or production. The browser server at port 8000 was unavailable during verification.

## September 2026 reference refresh

### Complete reference inventory and mapping

Reviewed the updated inventory: **136 HTML pages, 17 JavaScript files, five stylesheets and 16 images**. The original 16 pages remain mapped above. The 120 additions are five property variants × twelve months × English/Arabic. All 120 are implemented by one parameterized report view, allowing any assigned property and saved year rather than hard-coding five names or 2027. Reserve/Square/East contain the commercial component layout; Dunes/West omit unavailable retail figures and include source-total reconciliation. The integrated view supports all six optional components and reconciliation for every property.

| Reference change | Integrated behavior |
| --- | --- |
| Landlord dashboard property pills, nested sidebar and two-column property summaries | Assigned-property navigation; latest published monthly totals, office/retail occupancy, collections, area and P&L |
| Report property/month selectors and English/Arabic links | Authorized property URLs, twelve month links, saved-year selector and existing persisted locale switch |
| Rental revenue, service charges, total revenue, collection rate | Calculated from saved component figures and collection amounts |
| Annual revenue bars and occupancy lines | Twelve-month SVG charts with selected-month indicator, exact accessible tables, tooltips and gaps for missing data |
| Collection and rental-mix donuts | Saved amounts and calculated percentages, with visible exact-value legends |
| Component revenue, property facts, area, service-charge and rental-rate bars | Six optional components: office, mezzanine, lobby/corridors, terrace, retail and outdoor |
| Detailed monthly source table and annual Summary worksheet | Editable source labels, cell references and six value columns, preserved separately from calculations |
| Forecast, reconciliation and source notes | Office/retail forecasts, saved versus calculated total and difference, workbook name and notes |
| Collapsible P&L example | Same disclosure, KPIs, revenue/expense chart, expense donut and monthly results, populated from saved real figures |
| Role buttons/popups on all staff and landlord pages | Shared native dialog, role-specific EN/AR guidance, Escape/backdrop close and restored trigger focus; also available to admin/manager |
| Supplied CSS, responsive rules, scrollbar and print treatment | Synchronized reference CSS with scoped application adaptations and printable expanded report details |

The prototype hides its former landlord KPI/chart/recent-activity sections; the application now uses the replacement summary dashboard. Existing property, report, document and approval workflows remain available from the sidebar. The original staff/authentication HTML and supporting scripts retain their existing integrations; the new role dialog is supplied through the shared shell. No prototype script that replaces the document body, simulates authentication, inserts sample rows or overwrites real figures is imported.

### Data entry, publication and access

Migration `2026_09_10_180000_add_financial_reporting_to_workspace_items.php` adds nullable `report_month` and `financial_data` columns and a unique property/month index. Existing records and assignments are preserved. The migration was applied successfully to the **local XAMPP MySQL database**. Production must run `php artisan migrate --force` after deployment.

Use **Manage content → Create report** to select a property/month, enter components, collections, occupancy, annual forecasts, source tables and optional P&L amounts, and attach the source file. A month is required when financial fields are populated. Blank values are missing; explicit zeros are retained. Duplicate property/month submissions are rejected; use the existing report's edit form. Generic reports without a month continue to work.

Admins can preview monthly records, including drafts, at `/admin/properties/{id}/financials`; existing report View links route to this preview. Publish through the existing report status field. Only published, non-future records for assigned properties appear in landlord financial pages, charts, lists and downloads. Pending/approved/rejected statuses are visible to landlords only for approval requests, never as a way to expose an unpublished report. Direct routes and changed assignments are rechecked on every request.

The initial refresh omitted the reference dataset. The owner explicitly corrected this on 11 September and requested all supplied content. The restoration described below now imports the reference figures and retains their preview/demo labels. No original `.xlsx` workbook was supplied in `OUD_project/`; import extracts the saved values and cell references from the HTML. Notifications, manager financial submission/review stages and custom permissions described in the proposal-based role guide remain outside the existing workflow.

Calculations add supplied component rents and service charges once, and calculate collection rate only when rent due is positive. Missing monthly records do not become zero bars or connected occupancy segments. Source tables and annual summaries preserve entered values and references independently of charts, including discrepancies. Forecasts are not represented as net profit. P&L uses entered revenue and the three expense categories; the interface explains that incomplete categories produce incomplete totals.

### Refresh verification

- Local automated suite: **37 tests, 1,179 assertions passing**, using XAMPP PHP **8.2.4**, without the optional `intl` extension.
- Coverage includes all 120 property/month/locale combinations, blank/zero figures, source escaping, duplicate-month writes and edits, draft preview, role/property isolation, future publication and revoked assignments, plus existing authentication/content/approval tests.
- All five copied stylesheets and 16 copied images were verified byte-for-byte against the supplied reference folder.
- Laravel Pint formatted changed PHP. JavaScript syntax and Blade compilation were checked. Browser verification uses a separate SQLite fixture database and localhost preview; these checks do not constitute production verification.
- Headless Chrome verified desktop widths at 1,440px and mobile widths at 390px: dashboard, financial report, expanded details, Arabic desktop/mobile, role dialog and admin financial form. Document width equals viewport width in all eight layout checks. Reviewed screenshots after correcting RTL grid proportions; no JavaScript exceptions were recorded. Property/month navigation, browser Back, preserved sidebar DOM, language switching, role-dialog focus/Escape, and source-row add/remove controls passed.

## 11 September: restore the supplied content

The reported empty dashboard had no property assignments. The initial implementation also omitted the reference financial records, so layout-only verification did not establish that the owner's account had content. Both omissions are corrected.

- `ReferenceWorkspaceSeeder` imports five properties, 60 monthly financial records, the six general reports, three documents and six approval requests supplied by the reference screens/scripts. It assigns the reference properties to existing landlords without detaching other assignments. It does not change account credentials.
- `ReferenceReports` parses all source component values, monthly/annual rows and cell references into stored report data. Imported reports render the original English/Arabic main-content markup, preserving all charts, captions, exact tables, notes, expanded disclosures and labelled demo P&L sections. Only shell duplication and prototype authentication are removed; property/month links resolve to authorized Laravel routes and inaccessible properties are omitted. The trusted reference folder must be included in deployments.
- The dashboard displays the original January 2027 summaries, in the supplied property order. Administrator-created monthly reports take precedence when present. Editing an imported report through the content form removes its reference marker and uses the editable database-backed report view.
- Properties now open their property report instead of returning to the unchanged dashboard. Landlord lists have working property/search/category filters, report-period filtering, approval-status filtering, sorting, reset and pagination. The original six reports remain ahead of monthly records. Pagination was fixed for the larger populated dataset.
- Report details restore all four included-section entries and the separate download card. Approval details restore the property/amount/type/submitter fields, supporting-file link, comment and persisted approve/reject actions. Rerunning either import or the full seeder preserves final decisions.
- The HTML references name PDF/XLSX attachments but do not include the original binaries. Available reference text is exported into valid, clearly labelled PDF/XLSX previews for working downloads; these are not represented as the absent original contracts/proposals. Administrator-uploaded files are retained.

Applied locally:

```bash
php artisan oud:import-reference-content
php artisan oud:sync-design-assets
php artisan view:clear
```

The account shown in the owner's screenshot was checked against the local database after import: **5 assigned properties, 66 visible reports, 3 documents and 6 approvals**. This is local verification, not a claim about Laravel Cloud. After deploying, run the same import command in Cloud (or the existing `db:seed --force` deploy command, which now calls the importer).

Verification: **40 tests / 2,058 assertions passed**, including all 120 reference report variants, exact figures/source references, repeat imports, saved decisions, restricted links, category/search/status filters and valid downloadable file signatures. Headless Chrome verified populated dashboard/listings, search, report details/download, original report tables, month switching, Arabic desktop/mobile layout and saved rejection in an isolated database. Source HTML/CSS/JS/assets remain unchanged.
