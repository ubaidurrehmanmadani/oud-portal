# Design System Requirements

This document defines the shared design and system settings for the OUD Staff & Landlord Portal. These rules apply to every screen unless a specific feature request explicitly says otherwise.

## Document Maintenance Rule

When this document is updated, the existing content must be edited and expanded in place. Do not replace the whole document unless the user specifically asks for a full rewrite.

## Global Design Rule

All screens must use one consistent design system.

The same design language must be used across:

- Admin screens
- Department Manager screens
- Employee screens
- Landlord screens
- Login and authentication screens
- Profile and account settings
- Dashboards
- Forms
- Data tables
- Graphs and charts
- Sidebars
- Navbars
- Toolbars
- Modals
- Alerts
- Document libraries
- Report pages
- Approval workflows

Design differences between user roles are allowed only when the workflow requires them. The visual style, spacing, colors, typography, form controls, tables, navigation, and interaction patterns should remain consistent.

## Quality Standard

Every interface must be:

- Beautiful
- Professional
- Responsive
- Fast
- Compatible across modern browsers
- Easy to use on desktop, tablet, and mobile
- Clear for both English and Arabic users
- Consistent across all roles and modules

The design should feel like a polished business portal, not a basic admin template.

The portal design direction must align with the official OUD website: https://www.oud.sa/. Use the same luxury real-estate brand feel, including dark charcoal/black foundations, champagne-gold brand accents, refined spacing, understated borders, large editorial headings where appropriate, and restrained operational layouts for dashboards and forms.

## Consistency Rules

- Use the same color palette everywhere unless a change is specifically requested.
- Use the same button styles everywhere.
- Use the same input, select, checkbox, radio, toggle, and file-upload styles everywhere.
- Use the same table layout, spacing, status badges, row actions, filters, and pagination everywhere.
- Use the same chart and graph styling across all dashboards.
- Use the same sidebar, navbar, and toolbar patterns across role dashboards.
- Use the same card, modal, alert, dropdown, tab, and tooltip styling everywhere.
- Use the same profile/settings page style for every user role.
- Use the same spacing scale across all screens.
- Use the same border radius, shadows, borders, and hover states across all components.
- Use the same loading, empty, error, and success states across the system.
- Do not introduce a new visual style for one page unless the user specifically asks for it.

## Responsive Design Rules

- Every screen must work cleanly on desktop, tablet, and mobile.
- Navigation must remain usable on small screens.
- Tables must be readable on small screens through responsive layouts, horizontal scrolling, or compact mobile views.
- Forms must stack cleanly on mobile.
- Buttons and actions must remain easy to tap on touch devices.
- Text must not overflow or overlap.
- Charts must resize cleanly and remain readable on smaller screens.
- Modals must fit within mobile viewports.

## Performance Rules

- Pages should load quickly.
- Avoid unnecessary heavy UI libraries when existing project tools can handle the requirement.
- Optimize images before use.
- Avoid loading large videos directly into pages unless required.
- Use pagination, filtering, or lazy loading for large tables and document lists.
- Keep dashboards efficient so charts and metrics do not slow down the portal.

## Compatibility Rules

- The portal must support current versions of major modern browsers.
- UI behavior must be consistent in Chrome, Safari, Firefox, and Edge.
- The design must support both left-to-right and right-to-left layouts if full Arabic RTL support is required.
- Inputs, tables, charts, menus, and modals must remain usable in both English and Arabic.

## Colors

The color scheme should follow the official OUD website brand direction. These colors must be reused consistently across the full project unless the official brand palette is later provided by the client.

- Primary: Deep charcoal / near black, `#080806`.
- Secondary: Warm charcoal, `#171510`.
- Accent: Champagne gold, `#d8c3a5`.
- Accent dark: Antique gold, `#9f855f`.
- Success: Muted green, `#2f6f50`.
- Warning: Amber, `#b98226`.
- Danger: Deep red, `#9b2f2b`.
- Background: Warm ivory, `#f6f1e8`.
- Dark background: Near black, `#080806`.
- Surface: Soft ivory, `#fffaf1`.
- Dark surface: `#13110d`.
- Text: Ink charcoal, `#181612`.
- Text on dark: Warm ivory, `#f8efe3`.
- Muted text: Taupe gray, `#756b5f`.
- Muted text on dark: `#c8b79f`.
- Border: Soft sand, `#ded1bf`.
- Border on dark: Translucent champagne, `rgba(216, 195, 165, 0.24)`.

No page should introduce its own unrelated color palette without explicit approval.

## Typography

Typography should follow the official OUD website's refined real-estate tone while remaining practical for dashboard screens.

- English heading font family: `Georgia`, `Times New Roman`, serif, unless official licensed brand fonts are supplied.
- English body font family: `Inter`, `Segoe UI`, Arial, sans-serif.
- Arabic heading font family: `Noto Naskh Arabic`, `Amiri`, `Tahoma`, serif.
- Arabic body font family: `Tajawal`, `Noto Kufi Arabic`, `Arial`, sans-serif.
- Base font size: 16px for body text, 14px for dense labels and operational metadata.
- Heading scale: Use large editorial headings on authentication/brand screens; use tighter, smaller headings inside dashboards, cards, tables, and forms.
- Font weights: 400 regular body, 600 medium UI labels, 700 strong action labels, 500-700 headings depending on context.
- Letter spacing must be 0 for normal text. Use modest uppercase tracking only for short English eyebrow labels.

Typography must be consistent across all modules. Headings, labels, helper text, table text, button text, and dashboard metrics should follow the same type scale.

## Layout

- Page width: Public and auth content should use full-viewport compositions; operational screens should use a constrained content width near 1152px until detailed dashboards are defined.
- Sidebar behavior: To be defined for role dashboards.
- Header behavior: Auth screens should use the OUD logo prominently; dashboard headers should use the OUD logo at a smaller utility size.
- Grid and spacing scale: Use generous whitespace and restrained panels on auth screens; use compact, repeatable spacing on admin and operational screens.
- Mobile breakpoints: Auth forms must collapse to a single column with the OUD logo visible above the form.

Layouts should be structured, balanced, and easy to scan. Admin and operational screens should prioritize clarity, speed, and repeat use.

## Components

The following components must have one shared style across the project:

- Buttons
- Inputs
- Selects
- Textareas
- Checkboxes
- Radio buttons
- Toggles
- File upload controls
- Search bars
- Filters
- Data tables
- Pagination
- Graphs and charts
- Sidebar navigation
- Top navigation
- Toolbars
- Cards
- Modals
- Alerts
- Badges and status labels
- Tabs
- Dropdown menus
- Profile menus
- Settings panels
- Empty states
- Loading states
- Error states

## Icons And Imagery

- Icon style: To be defined, but icons must stay minimal and not compete with the OUD logo.
- Image usage: Use the official OUD logo from the official website in headers and authentication screens. Other imagery should match the official website's luxury property photography direction when client-approved assets are available.

Icons should use one consistent style throughout the portal. Property photos, training media, and document previews should be displayed cleanly and should not disrupt layout consistency.

## System Settings

These settings come from the project requirements and must be treated as global system rules during implementation.

### Language Settings

- The platform must support English and Arabic.
- Every user-facing screen should be planned for bilingual content.
- Arabic support should include right-to-left layout support if confirmed as required.
- Labels, buttons, tables, forms, charts, notifications, and validation messages must be ready for translation.
- Design spacing must allow longer translated text without breaking layout.
- Language and copy tone: professional, clear, and suitable for a business portal.

### Role And Access Settings

- The system has four main user roles: Admin, Department Manager, Employee, and Landlord.
- One login page must serve all roles.
- After login, users must be routed to the correct dashboard for their role.
- Each screen must show only the content the logged-in user is allowed to access.
- Admins have full system visibility and override rights.
- Department Managers see and manage only their own department.
- Employees see only their own department.
- Landlords see only their assigned properties.
- A landlord may access multiple properties.
- A property may be shared with multiple landlords.

### Department Settings

- Initial departments are Hospitality Management, Property Management, HR & Shared Services, Investment Management, Development Management, Commercial Division, Accountant Management, and Oud Academy.
- Admins must be able to add new departments later.
- Each department must have a private document library.
- Oud Academy is the training area for downloadable training files and videos.

### Property And Report Settings

- Landlord dashboards must support property KPIs, charts, reports, photos, documents, and approvals.
- Landlords with multiple properties need a property switcher.
- Monthly landlord reports must be retained permanently.
- Normal document updates replace the previous file unless the requirement specifically says history is needed.
- Reports should become visible to landlords only after super admin approval.
- P&L, budget, maintenance, and market reports are expected as downloadable PDF or Excel files in the first phase.
- Automatic report generation is a future enhancement unless approved for the current phase.

### File And Media Settings

- Supported file types are PDF, Word, Excel, PowerPoint, JPG, PNG, and MP4.
- The system must support documents, reports, contracts, invoices, certificates, approval attachments, property photos, training files, and training videos.
- Large videos should be handled carefully because they affect storage, hosting cost, and page speed.
- File upload, preview, replacement, and download interfaces must follow the same design pattern across the system.

### Notification Settings

- Email notifications are required when a new department document is posted for employees.
- Email notifications are required when a new landlord report is posted.
- Email notifications are required when a new landlord approval request is posted.
- In-app notifications are optional until confirmed.
- Notification UI must use the same style across all roles.

### Security And Audit Settings

- Production must use HTTPS.
- Login sessions must be secure.
- Passwords must be hashed.
- Access must be role-based and permission-based.
- File visibility must be permission-controlled.
- Audit logs are required for key user actions.
- Audit logs should include user creation/deletion, password resets, uploads, replacements, deletions, report approvals, announcement publishing, permission changes, and landlord approval decisions.

### Integration Settings

- Odoo ERP integration is part of the project scope.
- Exact Odoo integration points are not finalized yet.
- Possible integration areas are properties, tenants, financial figures, reports, approval workflows, users, and departments.
- Design and system architecture should avoid assumptions about Odoo data until the client confirms available modules, API access, and sync direction.

### Format Settings

- Date format: To be defined.
- Time format: To be defined.
- Currency format: To be defined.
- Number format: To be defined.

These settings must be applied consistently across dashboards, reports, tables, forms, and notifications.

## Accessibility

- Color contrast: To be defined.
- Focus states: To be defined.
- Keyboard behavior: To be defined.

The interface must remain readable, keyboard-accessible, and usable for normal business workflows.

## Implementation Notes

- Any new screen must follow this document before being considered complete.
- Any new component should reuse existing styles before creating a new pattern.
- If a screen requires a different visual approach, the reason must be explicit in the requirement before implementation.
- Design choices should be documented here once finalized, especially colors, typography, spacing, and component behavior.
