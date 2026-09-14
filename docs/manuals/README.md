# English and Arabic user manuals

Editable sources: `user-manual-en.html` and `user-manual-ar.html`. Deliverables: matching PDFs. Version 2 includes account suspension/restoration, queued recovery and visible audit inspection.

Per the owner's revised instruction, screen previews reuse existing application HTML. Do not generate or retain screenshot images. HTML source frames are populated at build time; opening a source without the renderer leaves preview placeholders empty.

Run a dedicated local headless Chrome instance on port 9238, then:

```sh
node scripts/render-user-manuals.mjs
```

The Node 22+ renderer calls `php scripts/render-manual-screens.php`. That helper explicitly switches to in-memory SQLite, migrates only that isolated database, creates synthetic users and renders actual controllers/Blade views. It removes screen scripts and external font links. Existing application assets are reused. Generated screen HTML is held in memory, injected into preview frames and printed, not stored as a growing image library. No live client data is used.

The renderer checks screen readiness and page overflow. Inspect PDFs after content changes. Close the dedicated Chrome instance after use. These are local rendered screens, not production or end-to-end browser verification. Update both languages at each completed daily milestone; follow the five-day plan in `docs/backend-delivery.md`.
