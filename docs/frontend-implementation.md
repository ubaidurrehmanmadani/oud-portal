# OUD frontend implementation

## Source of truth

Authentication screens share a 40% left brand panel and 60% form panel on desktop, stacking on mobile. Login, sign-up, forgot-password and reset-password use identical bottom-left brand copy. The login demo card stays open at the top-right beside the language switch, with copy controls always visible and no dropdown.

Maintain the integrated Laravel application directly. Screens live in `resources/views/`, styles/images/JavaScript in `public/oud/`, translations in `lang/`, and workflows in `app/` and `routes/`. No separate design-folder synchronization step is required.

The 120 English/Arabic monthly report resources in `resources/reports/` retain the complete report main content, charts, source tables, notes and labelled demo profit/loss. `App\Support\ReferenceReports` reads these application-owned resources for rendering and initial financial-data seeding. Include them in deployments. The shared Laravel layout supplies authentication, navigation, language switching and role guidance; report links are rewritten to authorized application routes.

## Screen mapping

Only Admin navigation groups links into collapsible People & access, Properties & reporting, Content management, Staff workspace and System administration categories. All existing links remain available; other roles retain their original navigation. Categories start expanded and support keyboard activation in both locales.

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

The login page includes a branded demo-access card with four role accounts from `config/demo-access.php`. Each email and the shared demo password has click-to-copy feedback and accessible status announcements. Readiness checks verify stored role, approval and password. At the owner’s request, the card is temporarily enabled in all environments, including production, and displays the four deployment Gmail accounts. Set `DEMO_ACCESS_ENABLED=false` and rebuild the configuration cache to hide it later; keep production `APP_ENV=production`. The login card does not provision accounts; deployment seeding provisions the accounts described below.

The requested local demo manager and employee belong to Property Management; the demo manager has financial submission permission and the manager/landlord are assigned the five demo properties. The landlord demo address intentionally uses `saad+landload@gmail.com` as requested; other existing landlord accounts are retained.

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
Text inputs and selects share the sign-up field styling (54px height, 8px corners, cream fill and desert border), including branded focus and autofill states. Textareas keep their larger height; checkboxes/radios use an olive accent without inheriting text-field dimensions. Login-specific brand copy uses dedicated translations.

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

## Deployment accounts — 13 September 2026

`DatabaseSeeder` now creates `admin@gmail.com`, `manager@gmail.com`, `landlord@gmail.com`, and `employee@gmail.com` with matching roles, approved status, profiles, and initial password `Test#12345` stored as a hash. Repeat seeding preserves these accounts and any changed credentials. Existing demo accounts remain. The landlord receives reference property assignments; manager department assignment and financial submission permission remain Admin-managed.

Set the server deployment hook to `composer deploy`, which runs `migrate --force` followed by `db:seed --force`. The existing documented Laravel Cloud two-command hook is equivalent. SQLite deployments require a persistent writable database path in server environment settings; local environment settings are unchanged. No schema or Blade changes are required. Local SQLite regression checks cover role links, successful login for all four accounts, and password preservation on repeat seeding. Server deployment configuration and production execution are not verified locally.

The desktop login layout uses a compact two-column demo account card and reduced heading/form spacing so the expanded local demo panel does not push login controls below a typical laptop viewport. Overrides are scoped to the login route; content remains reachable on smaller screens and at increased browser zoom rather than being clipped by hidden overflow.

Production demo card visibility is covered locally with production environment rendering, seeded account readiness, English/Arabic copy, and the disable switch. This does not verify the deployed server.

## Backend foundation and manuals — 14 September 2026

The owner's backend, queue, security, test and bilingual manual instructions are recorded in `docs/backend-delivery.md` and linked from `PROJECT_REQUIREMENTS.md` / `README.md`. Work proceeds by actual role order; this chunk is 1A, not completion of the entire Admin lifecycle or financial workflow.

Authentication POSTs now have shared email/IP and aggregate IP limits. Password recovery uses a uniform response and an encrypted after-commit notification with bounded retries on the `notifications` queue. Durable queue connections dispatch after commit. Account/department/property edits acquire a target-row lock and save an audit event in the same transaction. No migration or visual application change is needed.

Production recovery email now depends on a supervised queue worker and configured mail transport; see the backend ledger. General document/report notification delivery and file processing are still pending. Existing demo access behavior is preserved. Local checks do not verify production readiness, actual email delivery or concurrent MySQL locking.

English and Arabic version 1 PDF manuals in `docs/manuals/` cover current Admin setup navigation, account approval and recovery, with eight sanitized application-rendered screenshots and editable HTML sources. Regenerate from HTML using `node scripts/render-user-manuals.mjs` with a local headless Chrome page on port 9238. The renderer validates image readiness and page overflow before printing. Screenshots were generated from an isolated in-memory database, not production.

Local verification: full suite **58 tests / 2,313 assertions** passed, including new abuse-limit, reset queue/privacy, authorization, audit and rollback coverage. Laravel Pint and Blade compilation passed. No production deployment was performed.

## Day 1 Admin implementation and revised manuals

Applied pending local migrations and the additive `2026_09_14_180000_add_account_lifecycle_fields` migration. `/accounts/users/{user}/lifecycle` is an Admin-only POST for suspension, restoration or queued recovery, requiring the acting Admin password. User edit screens expose these controls in both languages. Suspended accounts cannot log in or use protected routes; restore preserves approval status. Database sessions are deleted on suspension; see the backend ledger for other-session-driver limits.

Setup creations are audited transactionally; updates retain safe before/after fields and assignment IDs. `/admin/audit-logs/view-audit-logs?source=changes` displays account/content audit events and expandable changes; login history remains available via the source selector. Local full suite: **66 tests / 2,357 assertions passed**, Pint and Blade compilation passed.

The owner revised delivery to five working days and requested HTML-based PDF previews instead of screenshots. `docs/backend-delivery.md` records daily scope and gates. Existing screenshots were removed; manual builds now render application Blade HTML into transient frames, reuse application assets and print both PDFs. No additional screenshot library is created. Previous screenshot-based manual notes above are historical and superseded by this section.

Manual revision verified: seven pages per language, direct HTML previews, no retained screenshots. PHP screen-render helper passes formatting, both PDFs pass readiness/overflow and page-count checks, and English/Arabic interior previews were visually inspected. Day 1 closed; later daily scopes remain pending as documented.

## Admin/Manager reporting continuation — 15 September 2026

The owner authorized continuing Admin/Manager beyond the prior day boundary. The implemented review queue is `/admin/report-reviews`, with download and POST decision routes guarded by Admin middleware. `resources/views/admin/reports/review.blade.php` shows pending/returned/rejected/approved reports; managers see comments/history and may correct returned reports. Approval publishes an immutable property/month report and dispatches notifications. Duplicate months conflict rather than overwrite.

Migrations now present locally: `2026_09_14_190000_create_report_reviews`, `2026_09_14_191000_add_setup_archiving`, `2026_09_14_192000_add_upload_processing`. On 15 September local MySQL `migrate` reports nothing pending. Archive/delete controls live on existing account edit screens. Deletion requires an archived, unassigned record without content/submissions.

Uploads enqueue `ProcessPrivateUpload`; protected content remains hidden until required processing completes. Workers must listen to `uploads,notifications,default`. Integrity processing calculates a streamed checksum; no malware scanning or automatic financial workbook parsing is claimed. Notification recipient access is rechecked before delivery; cleanup preserves referenced files and review originals.

Today's fixes preserve/show all approved management figures, dispatch notifications for already-processed drafts when published, and use freshly locked manager rows during updates. The financial report view shows management figures without inventing component breakdowns. The complete local suite passes **72 tests / 2,409 assertions**. Production delivery and load/concurrency acceptance remain unverified. See the current backend ledger for remaining requirements; earlier notes stating that Admin review was unimplemented are superseded.

## In-product manual access — 15 September 2026

All four role sidebars now include **User manual / دليل المستخدم**. `/help/manuals` offers English and Arabic open/download actions; `/help/manuals/en.pdf` and `/help/manuals/ar.pdf` serve the existing PDFs, with `?download=1` for attachment download. Authentication, account approval and suspension middleware protect the page and files. Locale allowlisting fixes file paths; responses use private/no-store caching and nosniff. No copying to public storage or PDF generation occurs on requests. Deploy `docs/manuals/user-manual-en.pdf` and `docs/manuals/user-manual-ar.pdf` with the application. Missing packaged PDFs return 404.
