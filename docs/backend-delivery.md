# Backend delivery and architecture ledger

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
| 1B | Complete Admin lifecycle: creation, archive/removal rules, password administration, richer permission audit, setup validation | Pending full gap audit and implementation |
| 2 | Department publishing, private staged uploads and queued processing/notifications | Existing basic publishing; processing and delivery pending |
| 3 | Employee scoped consumption and notifications | Existing basic access; delivery integration pending |
| 4 | Manager monthly drafts/submission, secure upload processing | Existing submission; processing enhancements pending |
| 5 | Admin financial review, return/correction/resubmit, approved publication | Pending; reviewer uses existing Admin role until a separate level is confirmed |
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
php artisan queue:work --queue=notifications,default --sleep=1 --tries=3 --timeout=60 --max-time=3600
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
