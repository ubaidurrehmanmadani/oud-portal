# Step-by-step acceptance testing guide

Prepared 16 September 2026 against the current application source. Follow the sections in order. This is a manual acceptance checklist, not a record that these tests have been performed on your deployed site. Record each result as Pass, Fail or Blocked, with the account, URL, language and observed result.

## 1. Prepare your test session

1. Use a local or staging copy and create records prefixed `TEST`. Use only disposable records for removal tests.
2. Open the application and log in as Admin. The seeded account is `admin@gmail.com`, initial password `Test#12345`, if seeding ran and its password has not been changed. Otherwise use your current Admin credentials.
3. Use separate browser profiles for Admin, Manager, Employee and Landlord, or log out before switching accounts. Two tabs in the same browser profile share a login.
4. Prepare a small valid PDF, another PDF with different contents, and a valid XLSX file. Manager report uploads accept PDF/XLS/XLSX up to 20 MB; general content uploads accept supported file types up to 50 MB. Server upload limits can be lower.
5. For email checks, use addresses you control. The example addresses below are labels for local testing and cannot receive real mail.
6. Ask the environment operator to confirm a durable queue connection, configured mail transport and an active worker listening to `uploads,notifications,default`. The local worker command, from the project directory, is:

   ```sh
   php artisan queue:work --queue=uploads,notifications,default --sleep=1 --tries=3 --timeout=60
   ```

   A stopped worker can leave uploads processing and prevent report approval. Queue counts alone do not prove delivery. Do not change production environment settings just to run this walkthrough.

Expected: Admin opens the Admin dashboard. Worker/mail-dependent tests are marked Blocked if those services are unavailable; continue with other tests.

## 2. Admin: create departments and properties

1. Open **Departments → Create department**. Create `TEST Operations`, with a short description.
2. Create a second department, `TEST Other Team`, for isolation checks.
3. Open **Properties → Create property**. Create `TEST Building A`, with a location, type and 10 units. Leave landlords empty for now.
4. Create `TEST Building B` with 5 units.
5. Reopen each record, edit a description/location, save and refresh.

Expected: all four records persist with the saved details. Empty required names and invalid negative unit counts are rejected.

## 3. Admin: create the test accounts

Open **Users → Create user** for each row. Choose a test password of at least 12 characters and keep it for your test session. Do not create department heads as Admins.

| Name | Example email | Role | Department | Property assignment |
| --- | --- | --- | --- | --- |
| TEST Manager A | manager-a@example.com | Department Manager | TEST Operations | Building A, configured below |
| TEST Manager B | manager-b@example.com | Department Manager | TEST Other Team | Building B, configured below |
| TEST Employee A | employee-a@example.com | Employee | TEST Operations | None |
| TEST Employee B | employee-b@example.com | Employee | TEST Other Team | None |
| TEST Landlord A | landlord-a@example.com | Landlord | None | TEST Building A |
| TEST Landlord B | landlord-b@example.com | Landlord | None | TEST Building B |

1. Save each account and reopen it to verify the role and assignments.
2. For each Manager, use **Users → Edit**, select the department, enable **Allow financial report submissions**, select the appropriate reporting property and save. Financial submission permission is configured on the edit screen.
3. Edit Manager A again. In the separate **Permission overrides** form, set **Manage department employees → Allow**, enter your Admin password and save. Leave other capabilities at their role defaults. Manager B should retain the default employee-management denial.
4. Verify Landlord A is assigned only Building A and Landlord B only Building B. You can also manage landlord assignments through a property's edit screen.
5. Try creating another account with an existing email.

Expected: accounts and assignments persist; duplicate email is rejected. Manager property selections also persist when financial submission permission is unchecked, on both creation and editing. The unchecked permission still blocks financial submissions. Clear the property selection explicitly to remove an assignment. Managers have scoped access, not Admin access. Each form has its own Save action: saving account details does not submit the permission form.

## 4. Manager: publish documents, training and announcements

1. Log in as Manager A and open **Manage content** (`/content`).
2. Create a document named `TEST Operations Guide`, add text/category and a PDF, and save as **Draft**.
3. Log in as Employee A. Check Documents and search for the title: the draft must be absent.
4. Return to Manager A, edit the draft and change its status to **Published**. Allow the upload worker to finish.
5. As Employee A, open Documents, search for the title, open it and download the attachment. Verify the file contents.
6. As Employee B, search for the same title and try the item URL copied from Employee A. It must remain inaccessible.
7. As Manager A, replace the attachment with the second PDF, save, wait for processing and verify Employee A downloads the replacement.
8. Repeat creation/publication for `TEST Training` and a text-only `TEST Department Notice`. Employee A should see both in Training/Announcements; Employee B should not.
9. Exercise Manage content search, type/status filters and editing. Create an extra disposable document, open Edit, expand the removal control and confirm removal.

Expected: department scope applies to lists, direct links and downloads. Drafts and content awaiting required file processing stay hidden. Removed disposable content disappears. Training publication also queues email for eligible department employees after required file processing.

## 5. Admin: test targeted announcements

Open **Announcements → Create announcement**. For each test, enter a unique title, set **Published**, and choose the recipient mode below. Leave the publication date empty for immediate visibility. Search for people before filling the form, since the search reloads the page.

| Title | Recipient mode and selection | Expected non-Admin access |
| --- | --- | --- |
| TEST Everyone | Everyone | All approved, active test accounts |
| TEST Person | Selected people → Employee A | Employee A only |
| TEST Department | Selected departments → TEST Operations | Manager A and Employee A |
| TEST Property | Selected properties → TEST Building A | Landlord A |

1. Check each recipient and at least one excluded account, using both the list and a copied direct item URL. Admin can still see all items.
2. Edit `TEST Person`, replace Employee A with Employee B and save. Employee A loses access; Employee B gains access. This access change does not promise a new email for an already-published edit.
3. Choose Selected people without selecting anyone and save: validation must reject it.
4. Create an announcement with a future publication date. Recipients must not see it before that time. Check the environment's configured timezone.
5. Create a targeted draft and verify it is hidden; publish it and verify access.

Expected: only one targeting mode applies at a time. Property targeting reaches assigned landlords, not reporting managers. Everyone includes eligible landlords and staff. Manager publishing remains department-scoped; Managers cannot use the Admin targeting feature.

## 6. Manager: delegated employee administration

1. As Manager A, open **Manage department employees** (`/manager/employees`).
2. Create `TEST Delegated Employee` with a unique email and a password of at least 12 characters.
3. As Admin, verify the new account is an Employee in TEST Operations. The Manager form must not offer Admin/Manager roles or another department.
4. As Manager A, edit the new employee's name/email and save. Reopen and verify persistence.
5. Open the employee action form. Try suspension with a wrong Manager password: it must fail. Retry with the correct Manager password: it should succeed.
6. Restore the employee with your Manager password.
7. Try opening Employee B's edit URL as Manager A: access must be denied. As Manager B, try `/manager/employees`: access must be denied because delegation was not granted.

Expected: delegation is limited to Employees in the Manager's own active department. Recovery and removal are tested below.

## 7. Manager: save and submit a monthly report

1. As Manager A, open the report submission page (`/manager/reports/upload`).
2. Set title `TEST September Report`, property Building A, month September 2026, and attach a valid PDF/XLSX.
3. Enter occupancy `80`, gross revenue `100000`, net revenue `25000`, rent `50000`, and notes `TEST submission`. Save as draft.
4. Open My reports (`/manager/reports`), reopen the draft, change a figure and save. Download the saved attachment.
5. As Admin, check Financial report review: the draft must not appear in the review queue. As Landlord A, check Reports: no published report should exist yet.
6. As Manager A, reopen the draft and submit it. It becomes **Pending**, with editing locked.
7. As Manager B, try Manager A's submission/edit/download URL. Access must fail.
8. Try a new submission for the same Manager/property/month: it must be rejected as a duplicate. Try a wrong file type, a file larger than 20 MB and occupancy above 100: validation must reject them.

Expected: submitting does not publish to landlords. Files remain private. Excel figures are entered manually; uploading a workbook does not extract its values.

## 8. Admin and Manager: return, correct and approve

1. As Admin, open **Financial report review** (`/admin/report-reviews`), filtering Pending.
2. Inspect `TEST September Report`, its figures, author, property and downloaded attachment.
3. Select **Returned**, leave the comment empty and submit: validation must reject it.
4. Enter `Please correct net revenue`, then return the submission.
5. As Manager A, open My reports, filter Returned and open the report. Confirm the review comment appears.
6. Correct net revenue to `23000`, optionally replace the attachment, and resubmit. Status returns to Pending.
7. Wait for required file processing to complete. As Admin, select **Approved** and submit the decision.
8. Confirm the submission is Approved, history contains the return and approval, and the published report is listed under Reports.
9. As Manager A, reopen it: approved figures and file must no longer be editable. As Admin, attempts to edit the published submission-derived report through general content editing must be blocked.
10. For rejection testing, submit a separate October 2026 report. Reject it with a reason. Manager A sees the reason and cannot edit/resubmit that rejected report; use Returned when correction is required.

Expected: approval publishes only after processing succeeds and the attachment exists. Reviewed originals are retained internally. A second publication for an existing property/month is blocked rather than overwriting it.

Optional edge checks: use another unused month to publish occupancy `0` and a negative net revenue. Those values should remain visible rather than disappear. Omitted metrics should remain missing, not silently become zero.

## 9. Landlord: view reports, documents and financials

1. Log in as Landlord A. Confirm only Building A is assigned.
2. Open Reports, find the approved September report, inspect its management figures and download its attachment. Compare against the approved submission, including corrected net revenue `23000`.
3. Open the property's financial view and choose the applicable month where available. Management figures should be identified separately; detailed component figures must not be invented from totals.
4. As Landlord B, try the report and download links. Both must be inaccessible.
5. As Admin, create a published Document with audience Landlord and property Building A. After processing, Landlord A can open/download it; Landlord B cannot.
6. As Admin, remove Landlord A's Building A assignment and save. Refresh the report as Landlord A: access must be lost. Restore the assignment for later tests.

Optional reference-content checks: if reference data is already imported, open its existing property/month reports, switch English/Arabic and inspect tables/charts/downloads. Reference/demo figures are not your new test property's real results. Do not rerun seeding merely to perform this test.

## 10. Admin: create a direct financial report

1. Open **Reports → Create report**. Choose Building A and a month not used above, for example November 2026.
2. Enter a title, choose Published and fill selected financial component/summary fields. Enter source name/notes; add a monthly or annual source-table row.
3. Save, open the report as Landlord A and compare the entered values and source row. Check the Arabic rendering too.
4. Reopen this directly created report as Admin, change a value, save and verify the update. This is separate from the immutable Manager-submission publication flow.
5. Try another report for the same property/month: it should be rejected rather than create a duplicate monthly record.

Expected: the direct Admin report workflow persists structured figures and source tables. Input validation rejects invalid ranges.

## 11. Admin and Landlord: approval requests

1. As Admin, open **Approvals → Create approval**. Create `TEST Repair Approval`, property Building A, amount `1500`, a description and an optional attachment. Status is Pending.
2. After any required file processing, log in as Landlord A and open Approvals. Inspect the request, approve it and add a comment.
3. Refresh: the decision and comment persist. A repeated decision must not replace the saved one.
4. Create another request and reject it as Landlord A. Verify Landlord B cannot open/decide either Building A request.

Expected: the existing workflow stores one final decision per request. It does not implement voting or unanimous approval by multiple owners. Use one assigned landlord for this acceptance path; the multi-owner policy and subsequent execution handoff remain unresolved.

## 12. Admin: permission changes and content preview

1. Open **Users → Manager A → Edit → Permission overrides**. Set Manage department documents to Deny, confirm with the Admin password and save.
2. As Manager A, try creating/editing a document: the operation must be blocked even through a saved URL. Set the override back to Role default and verify access returns.
3. Repeat for Manage department training and Manage department announcements.
4. Set Manage department employees to Deny. Manager A must lose `/manager/employees` access. Restore Allow.
5. Separately uncheck **Allow financial report submissions** in account details and save. Manager A must lose financial submission access. Restore the permission and reporting property assignment.
6. For Landlord A, deny View property reports, save and verify report access is blocked. Restore it. Repeat separately for View property documents, Download property files, and Approve or reject property requests (use a fresh pending request).
7. Try saving a permission change with the wrong Admin password: it must fail.
8. As Admin, select **Preview visible content** from the user's edit screen. Check that its list follows assignments and permission changes. A suspended/pending account should have an empty accessible-content list.

Expected: overrides operate within the user's role and assignments. Preview is a read-only list, not a login as that user. Financial submission permission is separate from the override form. Role changes clear old overrides; test that only with an extra disposable account if needed.

## 13. Admin: suspension, restoration and recovery

1. Keep Employee A logged in in a separate browser profile.
2. As Admin, open Employee A's edit page and **Account access and recovery**. Try a wrong Admin password first, then suspend with the correct one.
3. Refresh Employee A's protected page and try logging in again: access must be blocked.
4. Restore Employee A as Admin. The old session must not regain access automatically; a fresh login should work. Restoration must not approve a pending account.
5. On your own Admin edit page, confirm there is no self-suspension action.
6. For an active approved account with a mailbox you control, select **Send password reset link**, confirming with your own Admin password.
7. With mail/worker services available, receive the message, follow its link and set a new password. The new password works; the old one does not. Existing protected sessions must be rejected after reset.
8. Log out and test **Forgot password** with a known email and an unknown email. Both should show the same generic confirmation. Only the eligible account should receive mail.
9. Manager A can request recovery for an active approved Employee in their department using the delegated action form and their own Manager password. Check that this cannot target Employee B.

Expected: requesting recovery does not immediately change the password. Admin/Manager never receive the recipient's new password. Real delivery remains Blocked if mail is not configured.

## 14. Public registration and Admin approval

1. Log out or use another browser profile. Open Sign up and register a new disposable Department Manager.
2. Confirm the pending message appears and the account cannot enter a protected workspace.
3. As Admin, open **Account approval requests** (`/admin/users/account-requests`). Inspect the applicant; edit their department assignment as needed.
4. Approve the application. Return to the applicant's browser and log in again: access should work.
5. Verify approval did not automatically enable financial reporting. Grant that separately only if required.
6. Register another disposable Manager and reject the request. That account must remain blocked. A decided request cannot be decided again.

Expected: public Manager/Admin requests need approval. Current Employee/Landlord registration follows a different path and does not automatically receive department/property assignments. Account-approval email delivery is not implemented.

## 15. Archive and removal rules — disposable data only

1. Create `TEST Empty Department` and `TEST Empty Property` without assignments/content.
2. Edit each, enter your Admin password in **Archive and removal**, archive it, restore it, then archive again and select **Delete unused record**. It should disappear.
3. Archive TEST Operations temporarily. Manager A must be unable to create new department content or save/submit reports. Attempt deletion as Admin: assigned users/history must block it. Restore the department.
4. Archive Building A temporarily. New Manager report submissions for it must fail. Deletion must be blocked by assignments/history. Restore the property. Archiving does not erase existing records or universally hide all existing content.
5. Create `TEST Unused User` as an Employee with no department/property assignments. Never log in as this account or select it as an announcement recipient.
6. As Admin, suspend it, then use **Delete unused account**, enter the Admin password, tick the permanent deletion confirmation and submit. It should be removed.
7. Suspend Employee A and try deletion. Its assignments/login history must block removal. Restore it afterward.
8. As Manager A, create another Employee and never log in as that Employee. Suspend it, select Delete, enter the Manager password and tick confirmation. An otherwise unused employee can be removed within the delegated department. History-bearing or targeted employees must be retained instead.

Expected: restoration preserves history; destructive removal is restricted. Admin removal requires no assignments. Delegated removal makes a specific exception for the employee's own department assignment, but still checks history and other references.

## 16. Audit, background processing and notifications

1. As Admin, open **Audit logs**, select **Account and content changes**, then Search. Inspect creation, assignment updates, permission updates, suspension/restoration and report actions from this walkthrough. Expand available change details.
2. Switch to **Login activity** and verify recent logins/logouts/recovery events where applicable. Password values must not appear in audit output. Not every event has a field-by-field difference; some store the action and target only.
3. Open **Notifications** (`/admin/notifications/view-notifications`). Check database queue counts if available and any failed-job metadata. This screen is operational monitoring, not a per-user notification inbox.
4. With working mail and workers, publish a new department document/announcement and verify Employee A receives it while Employee B does not. Test a targeted announcement and approved property report similarly. Ordinary edits to an already-published item do not promise another email.
5. If a supported failed upload/cleanup/publication job exists, have the operator resolve its cause, then use Retry job with your Admin password. Verify work completes; do not keep retrying an unresolved failure.
6. Password-recovery and unsupported failures should request operator review rather than offer unrestricted retry. Raw payloads and exception messages must not be displayed.

Optional operator-only staging check: pause the isolated staging worker, upload a file and verify recipient visibility/report approval stays blocked, then resume the worker and verify processing completes. Do not stop shared production workers. Do not corrupt actual files or queue records to create a failure.

Expected: files are checked for readability/size and hashed in the background; this is not antivirus scanning or spreadsheet extraction. Notifications recheck access before delivery. Mail can be delivered more than once around retries; exactly-once delivery is not guaranteed. Mark failed-job retry Not exercised if no suitable failure exists.

## 17. Language, navigation, manuals and access boundaries

Select controls: on user/property edit and announcement forms, click a dropdown and type to search. In Properties, Landlords or recipient multi-selects, click multiple results without holding Ctrl/Command; remove one using its × control. Save and reopen to confirm remaining selections persist. Check keyboard navigation, Arabic direction, narrow screens and navigation to another form without a full page reload. Existing None/All/default options should remain available on single-select fields. With JavaScript unavailable, native selects remain the fallback.

1. As each role, switch between English and Arabic and repeat representative list/detail/form checks. Verify saved data persists through a language change.
2. Check a narrow/mobile window: navigation, forms, tables and action buttons remain usable. Inspect Arabic direction and labels.
3. Open **User manual** in each role's sidebar. View English and Arabic PDFs in new tabs; test the separate Download buttons. Both current manuals contain 15 pages.
4. Copy a PDF URL and try it while logged out: authentication must be required. Suspended/pending accounts must not gain manual access.
5. As Manager, Employee and Landlord, try an Admin URL such as `/admin/users/view-users`: access must be denied. Test copied private item/download links across departments and properties as above.
6. On an isolated test account, repeated invalid login attempts should eventually be throttled. Wait for the short rate-limit window before retrying; do not perform this on a shared administrator login.

Expected: authorization applies on the server, not just by hiding menu links. For denied tests, a Forbidden/Not found response can be correct. Unexpected errors on authorized actions should be recorded with the exact URL/action.

## Implemented scope checklist

| Area | Implemented behavior | Walkthrough sections |
| --- | --- | --- |
| Authentication | Role login/logout, registration, approval gates, throttling, queued recovery, session revocation | 1, 13, 14, 17 |
| Admin setup | Department/property/user creation and editing, role/assignment management | 2, 3 |
| Account lifecycle | Suspension/restoration, password-confirmed recovery, restricted deletion | 13, 15 |
| Setup lifecycle | Archive/restore and removal of unused departments/properties | 15 |
| Permissions | Supported Manager/Landlord overrides, separate reporting grant, read-only content preview | 3, 12 |
| Delegation | Department-scoped Employee creation/editing/lifecycle | 6, 13, 15 |
| Content | Documents/training/announcements, drafts/publication, search/filter/edit/removal, private files | 4 |
| Targeted notices | Everyone/person/department/property recipients, scheduling and access rechecks | 5 |
| Manager reporting | Private drafts/uploads, manual metrics, submit, return/correct/resubmit, history | 7, 8 |
| Admin reporting | Review/return/reject/approve, immutable submission publication, duplicate-month protection | 8 |
| Direct financial reports | Structured Admin-entered figures/source rows, bilingual financial views | 9, 10 |
| Landlord workspace | Assigned properties, reports/documents, private downloads, existing request decisions | 9, 11 |
| Employee workspace | Scoped consumption, search and downloads | 4, 5 |
| Background work | Private upload processing, publication/recovery mail, referenced-file-aware cleanup, supported retries | 4, 8, 13, 16 |
| Audit | Login history and account/content/permission/report action records | 16 |
| Manuals/UI | EN/AR application and manuals, separate PDF view/download, responsive layouts | 17 |

## Not implemented or not established by these tests

- Automatic Excel-to-financial-field extraction and malware scanning service.
- Odoo synchronization; credentials, modules and the sync contract remain outstanding.
- Multi-owner voting/approval policy and execution handoff after a landlord decision.
- Arbitrary custom role/permission definitions; overrides are limited to supported capabilities.
- Account-approval emails and a full user notification inbox.
- Guaranteed uptime, load capacity, backup/restore readiness, real mail delivery or production concurrent-lock behavior. Those require separate environment acceptance evidence.

Settings/integration/status navigation does not by itself mean every displayed integration or configuration operation is implemented. Use the checklist above as the implemented scope.

## Result template

Copy a row for each test you run. A listed test is not a passed test until you perform it in your environment.

| Section/step | Environment | Account | Language | Expected result | Actual result | Pass / Fail / Blocked |
| --- | --- | --- | --- | --- | --- | --- |
| | | | | | | |
