# Project guidance

## Frontend designs

- `OUD_project/` at the project root contains the frontend developer's screens and is the design reference for role-specific application screens.
- When implementing or updating a screen, inspect and adopt the corresponding design, styling, assets, and interactions, integrating real application data, workflows, and role permissions.
- The project owner may update or replace the entire folder. When notified and asked to update the frontend, inspect the latest contents and apply the relevant changes to application screens.
- Preserve the supplied reference designs unless asked to edit them; implement integrated screens in the appropriate application files.
- See `README.md` for the documented frontend design convention.

## Frontend implementation scope

- Frontend work includes the necessary routes, controllers, database integration, and role/assignment checks, as explicitly requested by the project owner.
- Keep `docs/frontend-implementation.md` current with screen mappings, migrations, verification, and material limitations.
- `public/oud/styles.css` and `public/oud/assets/` are synchronized reference assets. Keep application adaptations in `public/oud/application.css` and Blade views; do not import prototype authentication or sample-data JavaScript.

## Environment distinction

- Laravel Cloud screenshots may describe production failures. Inspect local source to prepare fixes, but never describe local checks as production verification. Keep local `.env` settings separate from Cloud environment variables.
