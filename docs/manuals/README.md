# English and Arabic user manuals

Editable sources: `user-manual-en.html` and `user-manual-ar.html`. Deliverables: matching PDFs. Version 2 includes account suspension/restoration, queued recovery and visible audit inspection.

Per the owner's revised instruction, screen previews reuse existing application HTML. Do not generate or retain screenshot images. HTML source frames are populated at build time; opening a source without the renderer leaves preview placeholders empty.

Run a dedicated local headless Chrome instance on port 9238, then:

```sh
node scripts/render-user-manuals.mjs
```

The Node 22+ renderer calls `php scripts/render-manual-screens.php`. That helper explicitly switches to in-memory SQLite, migrates only that isolated database, creates synthetic users and renders actual controllers/Blade views. It removes screen scripts and external font links. Existing application assets are reused. Generated screen HTML is held in memory, injected into preview frames and printed, not stored as a growing image library. No live client data is used.

The renderer checks screen readiness and page overflow. Inspect PDFs after content changes. Close the dedicated Chrome instance after use. These are local rendered screens, not production or end-to-end browser verification. Update both languages at each completed daily milestone; follow the five-day plan in `docs/backend-delivery.md`.

Version 3 (15 September 2026) adds manager workspace, report submission/correction and Admin review/publication guidance. Ten pages per language. Synthetic manager/property/submission records are rendered in the isolated in-memory database; no attachment is presented as a client-supplied original. The source helper renders each screen with the appropriate role.

In-product access: after login choose **User manual / دليل المستخدم**, or visit `/help/manuals`. Both languages can be viewed and downloaded. Direct authenticated PDF URLs: `/help/manuals/en.pdf` and `/help/manuals/ar.pdf`; `?download=1` forces a download. Both PDF artifacts must ship with deployments.

Version 4 adds Admin removal of unused suspended accounts and its history-retention rule (eleven pages per language).

Version 5 adds targeted announcements, supported role permission overrides and content previews, delegated department employee management, and background queue monitoring/retries (fifteen pages per language). The 16 September continuation rebuilds these from the current Blade views with isolated synthetic data.
