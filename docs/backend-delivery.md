# Backend delivery and architecture ledger

## Continuation checkpoint — 16 September 2026

Resumed the existing uncommitted Admin/Manager implementation and verified it rather than restarting. This entry supersedes earlier statements that targeted announcements and delegated permissions are unimplemented.

- Admin announcements support everyone, selected people, departments or properties. Direct access and queued notification delivery recheck the recipient's current scope; drafts and future publications remain hidden.
- Admins can set role-default/allow/deny overrides for the supported Manager and Landlord capabilities, with password confirmation and audit history. Financial submission permission remains separate. Overrides do not bypass department/property assignments; role changes clear overrides. A read-only preview shows content visible to the selected account.
- Explicitly delegated Managers can create and edit Employees in their active department, and suspend, restore, request password recovery or remove unused suspended Employees. Sensitive actions require the Manager's password. Retained history and announcement references block removal. Delegated removal permits removal of the employee's own department assignment; Admin removal still requires an unassigned account.
- Admin Notifications now shows database queue counts and failed-job metadata without raw payloads or exceptions. Password-confirmed retries are restricted to supported upload, cleanup and publication jobs; recovery jobs require a fresh recovery request. Fixed queue counts to inspect the configured queue database/table, including custom connection names, rather than checking the application database.
- Suspension and completed password reset increment a session generation; old protected sessions are rejected after restore. Fresh login records the current generation.
- Local MySQL migration status confirms `2026_09_15_230000_add_announcement_targets_and_permissions` and `2026_09_15_231000_add_session_generation` have run. No migration or account-data mutation was needed today.

Verification: existing full isolated SQLite suite **87 tests / 2,646 assertions passed**. After the queue-monitor fix, the focused suite passed **3 tests / 17 assertions**, including a separate in-memory queue database, custom table and missing-table case. Pint, Blade compilation and JavaScript syntax checks passed. Manual version 5 includes the four additional workflows in English and Arabic.

Both version 5 PDFs were rebuilt successfully; HTML readiness/overflow checks and native PDFKit validation passed (15 pages per language). Diff whitespace checks passed. No screenshot files were retained.

Next work: employee consumption/notification acceptance checks, then the remaining landlord decision workflow. Multi-owner approval policy must be confirmed before changing decision semantics. Malware scanning, workbook mapping, Odoo contract/integration and production worker/mail/backup/load acceptance remain outstanding. Queue counts do not prove worker health or delivery; retrying external queues is not transactionally atomic with the application database. These local results do not verify Laravel Cloud.

## Owner instructions — 14 September 2026

These requirements apply to every subsequent implementation chunk:

- Build in real product order: administrator setup and access → department manager publishing → employee consumption → authorized heads' monthly submissions → administrative review/publication → landlord review and decisions.
- Deliver bounded, complete chunks. Record implemented behavior, tests, results, dependencies and unfinished work as work proceeds. Do not mark the whole backend complete when only one chunk is finished.
- Build for a real client: secure password hashing, authorization on every operation, private data, validation, transactional changes, auditability, performance and maintainability.
- Design relational data with foreign keys, appropriate deletion rules, uniqueness constraints and query indexes. Preserve existing records; use additive migrations and reviewed data transitions.
- Use queues from the beginning for expensive file processing, imports, report generation and notifications. Do not wait for traffic or request timeouts to introduce background work. Receiving/validating a bounded upload is separate from expensive processing; queue processing after private staging, then publish only when required checks succeed.
- Jobs need bounded retries/backoff, timeouts, safe retry behavior, transaction-aware dispatch, failed-job visibility and monitored workers. Queued work must not bypass current permissions or publication gates.
- Write meaningful success, validation, unauthorized access, failure and regression tests alongside implementation. Use isolated test data; never represent local tests as production verification.
- Maintain attractive English and Arabic PDF user manuals with actual application screenshots, updating them when each usable workflow is completed. Exclude credentials and client data; distinguish available features from planned ones.
- Target scalable, flexible, fast and continuously available operation. Availability and attack resistance require infrastructure verification, monitoring, patching, backup/restore testing and capacity testing; no claim of absolute protection or guaranteed uptime follows from local code tests.

## Delivery sequence

| Chunk | Scope | Status |
| --- | --- | --- |
| 1A | Authentication abuse limits, queued recovery email, assignment audit and transaction locking | Implemented; local verification below |
| 1B | Complete Admin lifecycle: creation, archive/removal rules, password administration, richer permission audit, setup validation | Setup lifecycle and audit implemented; broader permission/user-deletion gaps listed in latest update |
| 2 | Department publishing, private staged uploads and queued processing/notifications | Publishing and queued integrity processing/notifications implemented; production delivery unverified |
| 3 | Employee scoped consumption and notifications | Existing basic access; delivery integration pending |
| 4 | Manager monthly drafts/submission, secure upload processing | Submission, corrections and queued integrity processing implemented |
| 5 | Admin financial review, return/correction/resubmit, approved publication | Implemented using the existing Admin role; see latest update |
| 6 | Landlord reports and request decisions | Existing basic decisions; multiple-owner decision rule and execution handoff remain unresolved |
| 7 | Deployment resilience, load/security verification, Odoo integration | Pending environment and confirmed Odoo contract |

## Chunk 1A — implementation

- Authentication POST endpoints share limits of five requests per normalized email/IP per minute and thirty per IP per minute. The email portion of cache keys is SHA-256 hashed; this is key privacy, not password storage. Existing Laravel password hashing remains in place.
- Password recovery returns the same success response for known, unknown and broker-throttled addresses. This removes the explicit account-existence response difference; it is not a claim of constant-time execution.
- Recovery mail uses an encrypted queued notification on `notifications`, dispatched after commit, with three attempts, 30-second timeout and 10/30/60-second backoff. SMTP delivery is performed by the worker, not the request.
- Database, Redis, SQS and Beanstalk queue connections defer dispatch until transaction commit. Rolled-back transactions must not dispatch their notifications.
- Account, department and property edits lock the target row and persist their audit event in the same transaction as the edit/assignments. Audit stores actor, target, time, IP and bounded user agent, without passwords. Field-level before/after audit remains a follow-up.
- Existing role guards, approval guards, session regeneration, password hashing and assignment restrictions are retained. No database migration is required for this chunk.

## Queue and operations contract

Use a durable asynchronous queue connection in production, not `sync`, `null`, or a process-local fallback. Existing database jobs/failed-jobs migrations provide the initial backend. Run a supervised worker, for example:

```sh
php artisan queue:work --queue=uploads,notifications,default --sleep=1 --tries=3 --timeout=60 --max-time=3600
```

Keep `retry_after` above the worker timeout (existing database/Redis default: 90 seconds). Configure the mail transport, sender, shared APP_KEY and durable queue access for web and workers. Restart workers on deployment with `php artisan queue:restart`; supervise automatic restart and monitor failures and queue age. Failed encrypted reset jobs contain sensitive links: restrict queue access and do not retry expired reset links; request a fresh link instead. Reset expiry remains the password broker's configured value.

Multi-instance operation also needs shared sessions/cache (including throttle counters), private shared object storage, correct HTTPS/proxy configuration, health checks, database backups and restore tests. These have not been configured or verified on Laravel Cloud in this chunk. Existing owner-requested demo access remains unchanged; its removal and seeded account credential rotation must be addressed for client handover.

## Verification ledger

- Existing regression suite after initial changes: 52 tests / 2,253 assertions passed locally.
- New foundation checks: login throttling and time-window recovery; indistinguishable reset responses; encrypted, after-commit queued mail; forbidden non-admin edits; audit persistence without password disclosure.
- Final full-suite result: **58 tests / 2,313 assertions passed** locally; Pint, Blade compilation and diff whitespace checks passed. Both PDFs passed six-page checks and English/Arabic visual inspection.
- SQLite tests do not establish MySQL concurrent-lock behavior, real SMTP delivery, distributed throttling, load capacity or production availability. These remain explicit deployment acceptance checks.

## Manuals

See `docs/manuals/user-manual-en.pdf` and `docs/manuals/user-manual-ar.pdf`. Version 1 covers initial administrator navigation, setup, account approval and password recovery. Screenshots use application-rendered Blade pages and an isolated in-memory database; empty states are intentional. These are local rendered-page captures, not production or end-to-end browser verification. HTML sources are retained for incremental updates.

## Revised owner instructions and five-day plan — 14 September 2026

The owner explicitly requests spreading work across five working days, rather than completing every module today. Finish and verify each day's bounded scope before moving to the next. Reuse the application's existing Blade-rendered HTML in the manuals; do not create or accumulate separate screen images. This supersedes the earlier screenshot requirement. PDF and bilingual HTML guidance remain required. Daily work is session-driven; this document does not schedule unattended execution.

| Day | Work package | Completion gate |
| --- | --- | --- |
| 1 — current | Resolve pending local migrations; Admin setup creation audit, account suspension/restoration, queued recovery and visible change audit; replace manual screenshots with rendered HTML | Migration succeeds; authorization, validation, hashing, queue, access blocking and audit tests pass; EN/AR manuals updated |
| 2 | Finish Admin department/property archive and removal rules, relational constraints and assignment edge cases; manager department publishing and private upload staging/queued processing | Retention and relationship tests; invalid/oversized files rejected; processing retries tested; no unprocessed publication |
| 3 | Employee scoped consumption and queued department notifications; authorized heads' report drafts/submission and background processing | Recipient/assignment isolation, retry behavior and draft privacy tests; bilingual manual updates |
| 4 | Admin report review, correction/resubmission and approved publication; landlord report visibility and request decisions | Transition/concurrency, retention and permission tests; resolve multi-owner approval rule before implementing dependent behavior |
| 5 | Cross-role regression, performance/security checks, deployment/worker monitoring and recovery checklist; integration readiness; final manuals | Record actual results and operational blockers; no production-ready claim without deployment verification |

Odoo implementation still depends on actual modules/API credentials/sync contract. Five days is a delivery plan, not permission to skip unresolved requirements or evidence. Track remaining work explicitly when an external dependency prevents completion. Avoid unnecessary rework through complete acceptance checks; do not promise that future defects or requirement changes are impossible.

## Day 1 implementation update

- Initial `php artisan migrate` applied three pending migrations successfully. No original migration defect reproduced locally. The next invocation hit a sandbox MySQL socket access restriction; rerunning with local socket access succeeded.
- Applied `2026_09_14_180000_add_account_lifecycle_fields`: indexed nullable `users.suspended_at`, JSON audit changes and chronological audit index. Existing data and approval decisions retained.
- Admins now use the user edit screen to suspend/restore accounts or queue password recovery, with their own current password required. The endpoint is Admin-only, CSRF-protected and throttled. Duplicate suspension/restoration conflicts; self-suspension is blocked. Admin lifecycle requests serialize Admin rows and recheck the acting Admin after locking.
- Suspension blocks login and existing protected access, rotates remember tokens and removes database-backed sessions when configured. Restore does not grant approval. Other session drivers remain blocked while suspended; persistent revocation across restore on non-database drivers requires further implementation.
- Recovery queues an encrypted after-commit notification; the Admin never sees or chooses the recipient's new password. This initiates recovery, not immediate credential invalidation or completed password reset. Mail delivery still requires production configuration.
- Department/property/user creation now includes an audit record in the transaction. Existing hashed-password, profile, role and assignment creation is retained. Updates record before/after safe fields and assignment IDs; password fields are excluded.
- Audit screen now offers Login activity or Account and content changes with expandable change details and pagination. Historical login records remain visible.
- Full local suite: **66 tests / 2,357 assertions passed**; Pint, Blade compilation and diff whitespace checks passed. Includes eight account lifecycle/creation/audit tests.
- Day 2–5 features remain pending. User suspension is not destructive user deletion. Department/property archive/removal is not claimed complete.

## Manual storage change

`render-user-manuals.mjs` calls `render-manual-screens.php`, which forces an isolated in-memory SQLite connection before migrations and fixture creation, reuses actual controllers/Blade views, disables demo credentials, and returns transient HTML. HTML is inserted into manual frames in browser memory and printed to PDF. No per-screen image or HTML snapshots are retained in the repository. Existing shared application assets are reused. Previous generated screenshot files were removed at the owner's request.

Day 1 close-out: both HTML-based PDFs regenerated successfully with seven pages each. Native PDFKit confirmed page counts; English and Arabic previews were visually inspected, including the complete account action panel. `docs/manuals/` contains only two HTML sources, two PDFs and its README; no screenshot files remain. The screen renderer uses temporary browser-memory HTML, with no persisted screen snapshots. Day 2 is not started in this session.

## Admin/Manager continuation — 15 September 2026

The owner's later instruction to complete Admin and Manager flows now overrides the earlier daily stopping boundary for these roles. Continue the existing work rather than restarting. The role migration referenced by the owner was inspected; no forward migration error reproduced. `php artisan migrate` with local MySQL socket access reports **Nothing to migrate**. No production migration or deployment is claimed.

### Current implemented workflow

- Admin setup, account approval, assignments, suspension/restoration and queued password recovery remain available. Department/property archive/restore and deletion of unused archived records are now implemented; assignments or retained content/submissions block deletion.
- Department managers can manage their own documents/training/announcements. Archived departments block new publishing. Authorized reporting heads can save drafts, submit, inspect review history, correct returned submissions and resubmit. Pending, rejected and approved submissions are locked.
- Admin financial review is available at `/admin/report-reviews`. Return/reject requires a comment. Approval requires completed file processing and an available attachment, and creates an assigned-property monthly report. Existing property/month records are not overwritten. The reviewed submission and its published report are linked; published submissions cannot be changed through content editing.
- Review decisions retain reviewer, timestamp, comments and submitted snapshots. Reviewed attachment originals are retained when corrected files replace them. Database foreign keys protect review/submission/publication history.
- New uploads are private and enqueue `ProcessPrivateUpload` on `uploads`. The worker streams the file to calculate SHA-256 and checks readability/size; stale/repeated processing jobs do not overwrite newer files or repeat publication dispatch. Required processing blocks landlord/staff visibility and report approval until successful. This is integrity processing, **not antivirus scanning or spreadsheet parsing**.
- New publication notifications are queued with recipient chunking; recipient role/property/department access is rechecked before email delivery. Unreferenced file cleanup is queued and checks content, submissions and review snapshots before deletion. Mail delivery is at-least-once; a provider interruption during retries can cause duplicates.

### Corrections completed today

- Approved reports now retain all submitted management metrics, including gross revenue and rent, and display them in a separate management figures section. Missing values stay missing; zero occupancy and negative net revenue survive publication. No unsupported financial breakdown is inferred from these totals.
- Publishing a draft whose file has already completed processing now queues its publication notification.
- Manager updates use the row fetched under lock, including its current attachment, instead of saving a stale pre-lock model.
- Queue worker documentation now includes the `uploads` queue; omitting it would leave uploads waiting indefinitely.
- English/Arabic manuals now include manager workspace/submission and Admin review, using actual temporary Blade-rendered HTML and synthetic data. No screenshot library is generated.

### Verification and remaining boundaries

Full local suite: **72 tests / 2,409 assertions passed**, including complete return/resubmit/approve/publication, immutable reports, duplicate months, archive restrictions, queue visibility/stale jobs, notification audience checks, approved metric rendering and processed-draft publication. Local SQLite tests do not establish live SMTP delivery, MySQL race behavior under load, malware detection or production availability.

Not claimed complete: physical user deletion with history policy, general custom permission overrides/delegated manager user administration, person-targeted announcements, malware scanning service, automatic workbook mapping, Odoo integration, multi-owner approval policy, and production worker/monitoring/backup/load acceptance. Existing user suspension provides access removal without destroying history. These outstanding requirements must remain tracked; the completed reporting chain does not mean every project requirement is finished.

Manual validation: version 3 PDFs regenerated directly from current Blade HTML; both contain ten pages. Native PDFKit page counts and English/Arabic review-page visual checks passed. Preview-only framing focuses the review controls without changing application views. Formatting, Blade compilation, JavaScript syntax and diff whitespace checks passed. No screenshots are stored in the manual directory.

## In-product user manual — 15 September 2026

Added a shared sidebar link and authenticated `/help/manuals` page with both language PDFs. PDF routes support inline viewing and explicit download, use allowlisted filenames and private/no-store/nosniff responses, and preserve approval/suspension guards. Local focused verification: **2 tests / 97 assertions passed**, covering all roles, both locales, navigation, downloads, invalid locale and blocked/guest access. Pint, Blade compilation and whitespace checks passed. Deployment must include the PDF artifacts; no production deployment is claimed.

## Next Admin workflow: removal of unused users — 15 September 2026

Implemented permanent removal of unused suspended accounts via the existing Admin lifecycle endpoint. This conservative retention rule preserves established records: department/property assignments, login events, authored/decided content, submissions, reviews, audit activity and approval-review references block deletion. Active accounts and self-deletion are blocked. Admin authentication, current-password verification, explicit deletion confirmation, CSRF and lifecycle throttling apply. The target is locked inside the existing lifecycle transaction; successful removal and its audit event commit together. Profile cleanup uses the existing foreign key; recovery tokens are removed so a later account with the same email cannot inherit them. No migration or deletion of real users was performed during implementation.

The user edit screen exposes a separate bilingual deletion form only for suspended accounts other than the acting Admin. Successful deletion returns to the Users list. Accounts with retained history continue to use suspension; broad historical-data erasure is not implemented. Added regression tests for removal/profile/token cleanup, authorization/password/confirmation, history preservation, bilingual UI and rollback on audit failure. Manual version 4 documents this workflow in both languages.

Verification: full regression suite **78 tests / 2,528 assertions passed** after initial removal implementation. After adding protection for the target's own recorded account-approval decision, focused removal suite **5 tests / 26 assertions passed**. Pint, Blade compilation and whitespace checks passed. Both updated PDFs contain eleven pages and passed page-count checks. Accounts with an `approved_by` or `approval_decided_at` value are retained, in addition to accounts that approved others. No real account was deleted; these checks used isolated SQLite test data.
