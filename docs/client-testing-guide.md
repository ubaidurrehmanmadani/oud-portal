# OUD Staff & Landlord Portal — Client Testing Guide

This guide explains how to test the implemented application from initial setup through publishing, report approval and landlord decisions. No previous knowledge of the application is required.

This is a test procedure, not a statement that client acceptance or production verification has already passed. The account details below were checked in the owner's local database on 16 September 2026. Confirm the same accounts and data exist on the client testing environment before starting.

## Before you begin

- Application URL: the project owner must provide the client-accessible testing URL. `http://127.0.0.1:8000` works only on the machine running the local application; do not send it as a remotely accessible client link.
- The project owner must provide passwords privately. Use the existing password for each account; correcting the Manager's email did not change its password.
- Use separate browser profiles for different roles, or log out before switching. Tabs in one browser profile share the same login.
- Prefix new document/report/account titles with `TEST`. Do not delete existing business/reference records.
- Prepare two small PDFs with different contents and one valid XLSX. Report uploads accept PDF/XLS/XLSX up to 20 MB. General content uploads allow supported types up to 50 MB, subject to server upload limits.
- The environment operator must configure mail delivery and run workers for `uploads,notifications,default`. If processing or mail is unavailable, mark the dependent test Blocked and record the reason. A saved upload may remain hidden until processing completes.

## Roles and accounts

| Role | Responsibility |
| --- | --- |
| Admin | Creates accounts, assigns departments/properties, controls permissions, reviews financial submissions and publishes reports. Access spans the application. |
| Department Manager | Manages content for their assigned department. Financial submissions and employee administration require separate grants. |
| Employee | Reads and downloads published content available to their department or targeted directly to them. |
| Landlord | Views assigned property content and makes permitted approval-request decisions. |

| Name used in this guide | Login email |
| --- | --- |
| Admin | `admin@gmail.com` |
| Property Manager | `ubaid+property_manager@gmail.com` |
| Hospitality Manager | `manager@gmail.com` |
| Property Employee | `ubaid+employee@gmail.com` |
| Hospitality Employee | `employee@gmail.com` |
| Ubaid Landlord | `ubaid+landlord@gmail.com` |
| Saad Landlord | `saad+landlord@gmail.com` |

The second Manager's saved display name is currently **Manager**; this guide calls it Hospitality Manager after the assignment below. The existing `landlord@gmail.com` account is also available but is not needed for the main sequence.

Existing departments: Property Management, Hospitality Management, HR & Shared Services, Investment Management.

Existing properties: OUD Reserve, OUD Square, OUD Dunes, La Perle East, La Perle West.

## 1. Admin — prepare the existing accounts

On a newly seeded deployment these assignments and permissions are now preconfigured. Review them rather than creating duplicate accounts. The restricted Landlord in step 2 is also precreated as `ubaid+restricted_landlord@gmail.com`; use its existing account. Repeat seeding preserves later test changes, so verify the settings before a new test round.

1. Log in as Admin and open **Users**.
2. Edit **Ubaid Property Manager**. Confirm the corrected email `ubaid+property_manager@gmail.com`, Department Manager role and Property Management department.
3. Keep OUD Reserve and OUD Square selected. Enable **Allow financial report submissions** and save.
4. In the separate **Permission overrides** form, set **Manage department employees → Allow**, enter your Admin password and save this form too.
5. Edit `manager@gmail.com`: select Department Manager, Hospitality Management, OUD Dunes and enable financial submissions. Save. Leave employee management at its default denial.
6. Edit `ubaid+employee@gmail.com`: select Property Management and save.
7. Edit `employee@gmail.com`: select Hospitality Management and save.
8. Reopen the records to check that settings persisted.

Expected: the two teams have different department access. Property assignment does not automatically enable reporting. The two Save buttons submit separate account and permission forms.

If Manage content says a department assignment is required, verify which Manager is logged in and complete that account's assignment.

## 2. Admin — prepare an excluded Landlord for access tests

The existing Ubaid, Saad and generic Landlord accounts currently have all five properties. They should all be able to access published OUD Square content. Do not treat that as a failed isolation test.

1. Open **Users → Create user**.
2. Create **TEST Restricted Landlord**, with a unique email supplied by the owner and a password recorded privately.
3. Select Landlord and assign **OUD Reserve only**. Save and reopen to verify.

Expected: this account can be used to check that OUD Square content is inaccessible, without changing existing Landlords' assignments.

## 3. Property Manager — create a draft document

1. Log in as `ubaid+property_manager@gmail.com`.
2. Open **Manage content → Create · Document**.
3. Enter `TEST Property Management Guide`, a description and category, and attach the first PDF.
4. Select Draft and save.
5. Reopen the document and confirm its details.
6. Log in as Property Employee. Open Documents and search for the title.

Expected: the Manager can edit the draft, but Employees cannot view it yet. The existing published **TEST DOCUMENT** can also be inspected; use the new document for the draft test.

## 4. Publish, download, replace and remove content

1. As Property Manager, edit the draft and change its status to Published. Save and wait for processing.
2. As Property Employee, search for it, open it and download the PDF.
3. Copy the item's URL. As Hospitality Employee, search for it and try the copied URL.
4. As Property Manager, replace the attachment with the second PDF. Save and wait for processing. Download it again as Property Employee and compare the contents.
5. Test Manage content search and type/status filters.
6. Create an extra document named `TEST Disposable Document`, open Edit, expand the removal control and confirm removal.

Expected: Property Employee can access the published file; Hospitality Employee cannot. The replacement downloads correctly. The disposable document disappears after removal.

## 5. Test training and department announcements

1. As Property Manager, create and publish `TEST Property Management Training` and `TEST Property Management Notice`.
2. As Property Employee, check Training/Oud Academy and Announcements.
3. As Hospitality Employee, check the same lists and copied links.
4. As Hospitality Manager, try editing the Property Management content using a copied edit URL.

Expected: Property Employee sees the published items. The other department cannot consume or manage this scoped content. Training publication queues email for eligible department employees; delivery requires configured mail and running workers.

## 6. Admin — target announcements

Open **Announcements → Create**. Enter a unique title, select Published, and use each mode below. For immediate publication, leave the publication date empty.

| Title | Recipient mode | Selection | Expected non-Admin access |
| --- | --- | --- | --- |
| TEST Everyone Notice | Everyone | None needed | All approved, active test accounts |
| TEST Person Notice | Selected people | Property Employee | Property Employee only |
| TEST Department Notice | Selected departments | Property Management | Property Manager and Property Employee, plus any other staff later assigned there |
| TEST Square Notice | Selected properties | OUD Square | All Landlords assigned to OUD Square |

1. Check an intended recipient and an excluded account for every notice. Admin retains oversight.
2. Confirm TEST Restricted Landlord cannot open TEST Square Notice.
3. Edit TEST Person Notice: replace Property Employee with Hospitality Employee. Confirm access moves to the selected account.
4. Test a draft and a future publication date. Recipients should not see either prematurely. Check the environment's configured timezone.
5. Try Selected people with no person selected: validation should reject it.

Search for recipients before filling the announcement form because recipient search reloads the page. An edit to an already-published notice does not necessarily send another email.

## 7. Property Manager — manage Employees

1. Open **Manage department employees**.
2. Confirm Property Employee appears and Hospitality Employee does not.
3. Create `TEST Property Assistant`, using a unique email and password of at least 12 characters.
4. Edit the name and save.
5. Suspend the assistant using your Manager password, then restore it.
6. Repeat a sensitive action with a wrong password: it should fail.
7. Log in as Hospitality Manager and try `/manager/employees`.

Expected: the assistant is automatically an Employee in Property Management. The Manager cannot choose an Admin role or another department. Hospitality Manager is denied employee administration because it was not granted.

## 8. Property Manager — submit a monthly report

Open **Financial submissions → Upload Excel / PDF report** and enter:

| Field | Value |
| --- | --- |
| Title | TEST OUD Square September 2026 |
| Property | OUD Square |
| Month | September 2026 |
| Occupancy | 80 |
| Gross revenue | 100000 |
| Net revenue | 25000 |
| Rent | 50000 |
| File | Valid PDF or XLSX |

September 2026 was unused when this guide was prepared. Check before starting if another tester has since used it; choose another unused property/month if necessary. Imported monthly reference reports currently occupy 2027.

1. Save as Draft. Reopen, change the notes and download the saved attachment.
2. Check as Admin: the draft should not appear in the review queue. Check as Landlord: it should not be published.
3. Submit as Property Manager. Confirm Pending status and locked editing.
4. As Hospitality Manager, try the copied submission and download URLs: access should fail.
5. Try creating another submission for the same Manager/property/month: validation should reject it.

Expected: submission remains private until approval. Excel upload does not extract financial values automatically; enter the metrics manually.

## 9. Admin and Manager — return, correct and approve

1. As Admin, open **Financial report review** and find the Pending September submission.
2. Inspect the figures and download its file.
3. Select Returned with no comment: validation should reject it.
4. Add `Please correct net revenue` and return it.
5. As Property Manager, open the returned report and confirm the comment appears.
6. Change net revenue to **23000** and resubmit. Optionally replace the file before resubmitting.
7. As Admin, wait for required file processing to finish, then approve it.
8. Confirm Approved status and the return/approval history.
9. Reopen it as Manager: editing should remain locked.

Expected: approval creates a published OUD Square report containing the corrected figures. The submission-derived publication cannot be changed through ordinary content editing. Existing property/month reports cannot be overwritten by another approval.

For rejection: submit a separate October 2026 report and reject it with a reason. The Manager sees the reason and cannot resubmit the rejected record. Use Returned when corrections are wanted.

## 10. Landlord — check reports and private files

1. Log in as Ubaid Landlord. Open Reports and find TEST OUD Square September 2026.
2. Compare the figures, especially net revenue **23000**, and download the file.
3. Open OUD Square's financial view. Submitted management figures should remain distinct from detailed component figures.
4. Log in as TEST Restricted Landlord and try the report and download URLs: both should be inaccessible.
5. As Admin, create a published document for audience Landlord and property OUD Square. Verify the same allowed/denied access after processing.

Expected: Ubaid and Saad Landlords have access because both are assigned OUD Square. The restricted account does not. Displayed reference/demo figures should not be interpreted as actual results of this test.

## 11. Admin — create a financial report directly

1. Open **Reports → Create report**.
2. Select OUD Square and another unused month, such as November 2026. Use title `TEST Direct OUD Square Report`.
3. Enter selected financial fields and source notes. Add a source-table row if desired. Save as Published.
4. As Ubaid Landlord, inspect the saved values and source information in English and Arabic.
5. As Admin, edit this directly created report and verify the update persists.

Expected: direct Admin reports are editable; this differs from the immutable publication linked to a reviewed Manager submission. A duplicate property/month is rejected.

## 12. Admin and Landlord — approval requests

1. As Admin, open **Approvals → Create approval**.
2. Create `TEST OUD Square Repair`, select OUD Square, enter amount **1500**, a description and optional attachment. Save.
3. As Ubaid Landlord, open the Pending request after any required processing and approve it with a comment.
4. Refresh: the decision/comment should persist. A repeated decision should not replace it.
5. Create a separate request and reject it.
6. As TEST Restricted Landlord, try the OUD Square requests: access should fail.

Expected: the current workflow stores one final decision per request. It does not implement voting/unanimous approval by multiple owners or an execution handoff after the decision.

## 13. Admin — permissions and preview

1. Edit Property Manager's Permission overrides. Deny Manage department documents, confirm with your Admin password and save.
2. As that Manager, try document creation/editing, including a saved direct link: it should fail. Restore Role default afterward.
3. Repeat for training and announcements.
4. Deny Manage department employees, confirm the page is blocked, then restore Allow.
5. Separately uncheck financial submissions in account details. Confirm report submission access is blocked, then re-enable it. Property assignments should persist while permission is off.
6. For Ubaid Landlord, separately deny and restore report viewing, document viewing, downloading and approval decisions. Use a fresh Pending approval request for the decision check.
7. Open **Preview visible content** as Admin and compare its list with that account's permissions/assignments.

Expected: overrides do not grant another department or an unassigned property. Preview is read-only; it does not impersonate the user. Restore each tested setting before continuing.

## 14. Suspension and password recovery

1. Keep Property Employee logged in in another browser profile.
2. As Admin, edit that account and suspend it using your Admin password.
3. Refresh its protected page and attempt a fresh login: both should be blocked.
4. Restore it as Admin. A fresh Employee login should work; an old session should not automatically regain access.
5. For an account with a mailbox you control, request **Send password reset link**.
6. With working mail/queues, follow the received link and set a new password. Verify the new password works and the old one fails.
7. Test Forgot password from the login page with a known and an unknown email. The confirmation should be generic in both cases.

Expected: requesting recovery does not immediately change the password. Completing it revokes old protected sessions. Admin never sees the new password. Manager recovery for an Employee in their department can be tested through the delegated action form using the Manager's own password.

## 15. Public signup and approval

1. Log out and register a new disposable Department Manager through Sign up.
2. Confirm the account remains pending and cannot enter a protected workspace.
3. As Admin, open Account approval requests, inspect the applicant and assign a department through account editing as needed.
4. Approve it, then log in as the applicant.
5. Register a different disposable Manager and reject it. That account should remain blocked.

Expected: approval does not automatically enable financial submissions. Account-approval email delivery is not implemented; the applicant returns to login after approval.

## 16. Archive and removal — disposable records

1. As Admin, create `TEST Empty Department` and `TEST Empty Property` with no assignments/content.
2. Edit each, archive, restore, archive again and choose Delete unused record, confirming with your Admin password.
3. Create `TEST Unused Employee` with no assignments and never log in as it. Suspend it, then delete it with your Admin password and explicit confirmation.
4. To test retention, create another disposable department and assign a disposable Employee. Archive the department and attempt deletion: it should be blocked. Restore it afterward.
5. To test account history retention, log in as a separate disposable Employee once, then suspend it and attempt deletion as Admin: it should be blocked by history.

Expected: unused records can be removed; history-bearing records are retained. Do not delete the existing OUD properties, departments or established accounts for this test.

## 17. Audit, queues, language and manuals

1. As Admin, open Audit logs. Review Login activity and Account and content changes, including permission and report actions. Expand available details; passwords must not appear.
2. Open Notifications to inspect queue counts/failure metadata. Counts do not prove worker health or delivered mail. It is an operations page, not a user notification inbox.
3. With working mail/workers, publish a new department document/announcement and verify eligible recipients receive mail while the other department does not. Verify report notification recipients similarly.
4. If a supported failed job exists, have the operator fix its cause before retrying with the Admin password. Recovery jobs need a fresh recovery link rather than unrestricted retry. Mark this Not exercised if no suitable failure exists.
5. Check representative forms/lists/reports in English and Arabic and at mobile width. Test dropdown search, multiple selections, removal, save and reopen.
6. Open User manual from each role's sidebar. Test English/Arabic View PDF and Download separately. Current PDFs contain 15 pages each.
7. Try Admin URLs as Manager, Employee and Landlord: access should be denied. Try private content URLs across departments/properties as above.

## Record results and report problems

For each test record Pass, Fail, Blocked or Not exercised. Send the project owner the screen/step, account role/email, URL, expected result, actual result, approximate time and screenshot. Never include passwords or reset links.

| Step | Account | Expected | Actual | Result |
| --- | --- | --- | --- | --- |
| | | | | |

Automatic Excel extraction, malware scanning, Odoo synchronization, multi-owner voting and execution handoff are outside the implemented test scope. Worker/mail delivery, backups, capacity and production availability need separate environment acceptance. File processing currently checks integrity/readability and computes a checksum; it is not antivirus scanning.
