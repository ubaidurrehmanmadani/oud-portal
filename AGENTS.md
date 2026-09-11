# Project guidance

## Application frontend

- The integrated application is the source of truth. Make future changes directly in Blade views, application assets, controllers, routes, and translations.
- Preserve existing content, styling, interactions, real application data, workflows, and role permissions when updating screens.
- `resources/reports/` contains application-owned English/Arabic monthly report content used for rendering and initial financial-data seeding. Include it in deployments.
- See `README.md` and `docs/frontend-implementation.md` for maintenance guidance.

## Frontend implementation scope

- Frontend work includes the necessary routes, controllers, database integration, and role/assignment checks, as explicitly requested by the project owner.
- Keep `docs/frontend-implementation.md` current with screen mappings, migrations, verification, and material limitations.
- Maintain styles, images, and JavaScript directly in `public/oud/`, with application-specific overrides in `public/oud/application.css`. Keep authentication and persisted workflows in Laravel rather than simulated client-side scripts.

## Environment distinction

- Laravel Cloud screenshots may describe production failures. Inspect local source to prepare fixes, but never describe local checks as production verification. Keep local `.env` settings separate from Cloud environment variables.
