# Project Requirements

Project: OUD Staff & Landlord Portal

Purpose: Build a secure bilingual web portal for internal staff and external landlords. The system will use one login page and route each user to the correct dashboard based on their assigned role and permissions.

## Document Maintenance Rule

When this document is updated, the existing content must be edited and expanded in place. Do not replace the whole document unless the user specifically asks for a full rewrite.

## Scope Summary

The platform will support four main user roles:

- Admin
- Department Manager
- Employee
- Landlord

Each role can have unlimited users. Access must be permission-based so users only see the departments, properties, files, reports, approvals, and announcements assigned to them.

The system must be bilingual in English and Arabic.

## Core Modules

- Authentication and secure login
- Role-based access control
- User management
- Department management
- Property management
- Document and media library
- Announcements
- Employee department portal
- Oud Academy training section
- Landlord dashboard
- Landlord reports
- Landlord approvals
- Email notifications
- Audit logs
- Odoo ERP integration

## User Roles

### Admin

Admins have full system control. The system must support one or many Admin accounts.

Admins can:

- Create, update, archive, and remove departments.
- Create, update, archive, and remove properties.
- Add and remove Admins, Department Managers, Employees, and Landlords.
- Reset passwords for any user.
- Upload, replace, and remove documents anywhere in the system.
- Upload and manage reports.
- Send announcements to all users, selected departments, selected properties, or selected people.
- Control file and report visibility.
- Override Department Manager and Landlord permissions.
- View department and property areas to confirm what users can see.
- Approve landlord monthly reports before they become visible to landlords.

Admins should not be responsible for entering landlord performance figures such as occupancy, revenue, or rent unless the business later changes this rule.

### Department Manager

Department Managers are limited administrators for their own department only. A department can have one or more managers.

Department Managers can:

- Access only their assigned department.
- Upload, replace, and remove documents for their department.
- Upload and manage training material for their department.
- Post announcements to their department.
- Manage department documents through a simple upload interface.

Department Managers cannot:

- Access other departments.
- Access properties or landlord data.
- Change system-wide settings.
- Create or delete user accounts unless an Admin grants that permission.

### Employee

Employees can access only their own department.

Employees can:

- Log in to a personal dashboard.
- View recent documents and announcements for their department.
- Browse their department document library.
- Download department documents.
- Access Oud Academy training files.
- Search department documents and announcements.
- Receive email notifications when new department documents are posted.

Employees cannot:

- Upload files.
- Edit files.
- Access other departments.
- Access landlord or property data.

### Landlord

Landlords can access only their assigned properties. A landlord can have access to multiple properties, and one property can be shared with multiple landlords.

Landlords can:

- View a dashboard for each assigned property.
- Switch between properties if they have more than one.
- View property KPIs such as occupancy, revenue, and other statistics.
- View charts for property performance.
- View property details.
- View tenant lists.
- View property photos.
- Download reports and documents.
- Review approval requests.
- Approve or reject requests with comments.
- View supporting documents attached to approval requests.
- Receive email notifications when a new report or approval request is posted.

Landlords can download sensitive documents, including contracts.

The identity of the person who uploaded property photos should not be visible to landlords.

## Departments

The initial departments are:

- Hospitality Management
- Property Management
- HR & Shared Services
- Investment Management
- Development Management
- Commercial Division
- Accountant Management
- Oud Academy

Admins must be able to add more departments later.

Each department needs its own private document library. Employees and Department Managers should only see content belonging to their assigned department.

Oud Academy is the training area. In the first phase, training will be downloadable files and videos. In a future phase, it may connect to a third-party e-learning system for tracked online courses.

## Property And Landlord Rules

- Admins can create and manage property records.
- Landlords can be linked to one or more properties.
- A property can be linked to more than one landlord.
- Landlords see only their assigned properties.
- Landlords should have a property switcher when assigned to multiple properties.
- Property dashboards should show on-screen KPIs and charts.
- P&L, budget, maintenance, and market reports can be uploaded as downloadable PDF or Excel files.
- Monthly reports must remain permanently available for landlord records.
- Reports should become visible to landlords only after super admin approval.

## Landlord Metrics And Reporting

The system should support manual monthly entry of landlord dashboard figures.

The people responsible for entering occupancy, revenue, rent, and similar figures are:

- Head of Property Management
- Head of Hospitality Management

Recommended reporting approach:

- Show current occupancy percentage.
- Show occupancy history over time.
- Show comparison against last year or budget where data is available.
- Show revenue as both a total and a breakdown.
- Include gross revenue and net revenue where available.
- Support breakdowns by unit type and lease where available.
- Include month-over-month and year-over-year comparison charts.
- Calculate core figures automatically where possible, while allowing manual entry for management-provided numbers.
- Allow PDF and Excel report uploads in the first phase.
- Treat automatic generation of financial reports as a future enhancement.
- Include a review step before monthly figures are published to landlords.
- Keep unusual-number alerts out of scope for the first phase.
- Landlords do not need chart export in the first phase if downloadable PDF reports are provided.

## Documents, Reports, And Media

Supported file types:

- PDF
- Word
- Excel
- PowerPoint
- JPG
- PNG
- MP4

The system must support:

- Department documents
- Training documents
- Training videos
- Property documents
- Contracts
- Invoices
- Certificates
- Monthly reports
- Quarterly reports
- Annual reports
- Approval request attachments
- Property photos

When a normal document is updated, the new file should replace the old file. Full version history is not required.

Exception: monthly landlord reports must be retained permanently and should not be overwritten.

Large video files may increase hosting and storage cost, so storage limits and hosting recommendations should be confirmed before implementation.

## Announcements And Notifications

Admins can send announcements to:

- All users
- A department
- A property
- Specific users

Department Managers can send announcements only to their own department.

Email notifications are required when:

- A new department document is posted for employees.
- A new landlord report is posted.
- A new landlord approval request is posted.

The system may also show in-app notifications if included in the final UI scope.

## Approval Workflow

Landlord approvals should support simple approve/reject decisions.

Approval requests may include:

- Budgets
- Maintenance requests
- Contracts
- Discounts
- Grace periods
- Special events
- Supporting documents

Landlords must be able to:

- Open the approval request.
- Review attached documents.
- Add a comment.
- Approve or reject the request.

The system should store the decision, comment, date/time, and landlord user who made the decision.

## Security Requirements

The platform must include:

- HTTPS in production.
- Secure login sessions.
- Password hashing.
- Role-based access control.
- Permission-based file visibility.
- Audit logs for key actions.

Audit logs should track important actions such as:

- User creation and deletion.
- Password resets.
- File uploads.
- File replacements.
- File deletions.
- Report uploads.
- Report approvals.
- Announcement publishing.
- Permission changes.
- Landlord approval decisions.

## Integration Requirements

Odoo ERP integration is included in scope.

The exact integration points still need confirmation. Possible integration areas include:

- Properties
- Tenants
- Financial figures
- Reports
- Approval workflows
- User or department data

The project should not assume the Odoo data structure until the client confirms available Odoo modules, API access, and required sync direction.

## Hosting And Technology

- Backend technology: PHP with Laravel.
- Hosting provider: to be recommended.
- Production must use HTTPS.
- Storage should support documents, images, and videos.
- Hosting plan should account for large files and expected user count.

## Implementation Phases

Estimated total timeline: approximately 8 weeks after kickoff.

### Phase 1: Frontend Development - 2 Weeks

- Login page.
- Role-based dashboards.
- Admin screens.
- Department Manager screens.
- Employee portal screens.
- Landlord portal screens.
- Bilingual UI layout checks.

### Phase 2: Backend Development - 3 Weeks

- Database structure.
- Authentication.
- Role and permission logic.
- Department management.
- Property management.
- User management.
- Document and report handling.
- Landlord dashboard data.
- Approval workflows.
- Email notifications.

### Phase 3: Integration And Testing - 2 Weeks

- Test all four roles.
- Test permissions and visibility.
- Test bilingual English and Arabic behavior.
- Test document upload and download.
- Test landlord reports.
- Test approvals.
- Fix issues from review.

### Phase 4: Deployment And Handover - 1 Week

- Deploy to live hosting.
- Configure production environment.
- Verify HTTPS.
- Final walkthrough.
- Handover to the client.

Timeline may change after the reporting dashboard requirements and Odoo integration details are finalized.

## Pending Decisions

The following items need confirmation before final technical planning:

- Exact landlord dashboard metrics.
- Exact revenue breakdowns.
- Whether occupancy and revenue data can be calculated from available system data or must be manually entered.
- Exact monthly report approval workflow and who approves each report.
- Whether there are multiple Admin levels, such as Admin and Super Admin.
- Odoo modules currently used by the client.
- Odoo API availability and authentication method.
- Data sync direction between Odoo and the portal.
- Final hosting provider.
- Expected number of users.
- Expected document and video storage volume.
- Whether in-app notifications are required in addition to email.
- Whether Department Managers can manage users in their departments.
- Required Arabic layout direction: full RTL support or translated text only.

## Out Of Scope For First Phase

Based on the current client proposal, these should be treated as future enhancements unless the client confirms otherwise:

- Third-party e-learning course tracking.
- Automatic generation of P&L, budget, maintenance, or market reports.
- Landlord export of on-screen charts.
- Automated alerts for unusual occupancy or revenue numbers.
- Full document version history for normal documents.
