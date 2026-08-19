<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function create(string $module): View
    {
        abort_unless(in_array($module, $this->formModules(), true), 404);

        $method = match ($module) {
            'users' => 'users',
            'permissions' => 'permissions',
            'departments' => 'departments',
            'properties' => 'properties',
            'documents' => 'documents',
            'academy' => 'academy',
            'reports' => 'reports',
            'approvals' => 'approvals',
            'announcements' => 'announcements',
            'notifications' => 'notifications',
            'integrations' => 'integrations',
            'settings' => 'settings',
        };

        $data = $this->{$method}()->getData();

        return view($this->formView($module), $data);
    }

    public function users(): View
    {
        return $this->screen('admin_users', [
            'title' => __('portal.admin_users_title'),
            'eyebrow' => __('portal.admin_users_eyebrow'),
            'description' => __('portal.admin_users_description'),
            'primaryAction' => __('portal.add_user'),
            'stats' => [
                [__('portal.total_users'), User::count()],
                [__('portal.admins'), User::where('role', 'admin')->count()],
                [__('portal.landlords'), User::where('role', 'landlord')->count()],
                [__('portal.pending_invites'), 6],
            ],
            'columns' => [__('portal.name'), __('portal.email'), __('portal.role'), __('portal.status')],
            'rows' => User::query()
                ->latest()
                ->limit(6)
                ->get(['name', 'email', 'role'])
                ->map(fn (User $user): array => [
                    $user->name,
                    $user->email,
                    $user->role->label(),
                    __('portal.active'),
                ])
                ->whenEmpty(fn (Collection $rows): Collection => $rows->push([
                    'OUD Admin',
                    'admin@oud.sa',
                    __('portal.admin'),
                    __('portal.active'),
                ]))
                ->all(),
            'cards' => [
                [__('portal.admin_users_card_1_title'), __('portal.admin_users_card_1_body')],
                [__('portal.admin_users_card_2_title'), __('portal.admin_users_card_2_body')],
            ],
            'tableTitle' => __('portal.user_accounts_table'),
            'formTitle' => __('portal.user_account_form'),
            'formFields' => [
                ['label' => __('portal.full_name'), 'placeholder' => __('portal.full_name')],
                ['label' => __('portal.email'), 'type' => 'email', 'placeholder' => 'name@oud.sa'],
                ['label' => __('portal.account_role'), 'type' => 'select', 'options' => [__('portal.role_admin'), __('portal.role_department_manager'), __('portal.role_employee'), __('portal.role_landlord')]],
                ['label' => __('portal.assigned_department'), 'type' => 'select', 'options' => $this->departmentOptions()],
                ['label' => __('portal.assigned_properties'), 'placeholder' => __('portal.assigned_properties_placeholder')],
                ['label' => __('portal.temporary_password'), 'type' => 'password', 'placeholder' => __('portal.temporary_password')],
                ['label' => __('portal.send_invite_email'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    public function permissions(): View
    {
        return $this->screen('admin_permissions', [
            'title' => __('portal.admin_permissions_title'),
            'eyebrow' => __('portal.admin_permissions_eyebrow'),
            'description' => __('portal.admin_permissions_description'),
            'primaryAction' => __('portal.review_permissions'),
            'stats' => [
                [__('portal.roles'), 4],
                [__('portal.permission_groups'), 9],
                [__('portal.override_rules'), 6],
                [__('portal.restricted_areas'), 12],
            ],
            'columns' => [__('portal.permission_area'), __('portal.admin'), __('portal.department_manager'), __('portal.landlord')],
            'rows' => [
                [__('portal.user_management'), __('portal.full_access'), __('portal.restricted'), __('portal.no_access')],
                [__('portal.department_documents'), __('portal.full_access'), __('portal.assigned_only'), __('portal.no_access')],
                [__('portal.property_reports'), __('portal.full_access'), __('portal.no_access'), __('portal.assigned_only')],
                [__('portal.audit_logs'), __('portal.full_access'), __('portal.no_access'), __('portal.no_access')],
            ],
            'cards' => [
                [__('portal.admin_permissions_card_1_title'), __('portal.admin_permissions_card_1_body')],
                [__('portal.admin_permissions_card_2_title'), __('portal.admin_permissions_card_2_body')],
            ],
            'tableTitle' => __('portal.permission_matrix_table'),
            'formTitle' => __('portal.permission_rule_form'),
            'formFields' => [
                ['label' => __('portal.role'), 'type' => 'select', 'options' => [__('portal.role_admin'), __('portal.role_department_manager'), __('portal.role_employee'), __('portal.role_landlord')]],
                ['label' => __('portal.module'), 'type' => 'select', 'options' => [__('portal.users'), __('portal.departments'), __('portal.properties'), __('portal.documents'), __('portal.reports'), __('portal.approvals')]],
                ['label' => __('portal.access_level'), 'type' => 'select', 'options' => [__('portal.full_access'), __('portal.assigned_only'), __('portal.read_only'), __('portal.no_access')]],
                ['label' => __('portal.override_scope'), 'type' => 'select', 'options' => [__('portal.global_scope'), __('portal.department_scope'), __('portal.property_scope'), __('portal.user_scope')]],
                ['label' => __('portal.override_reason'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.override_reason_placeholder')],
                ['label' => __('portal.log_permission_change'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    public function departments(): View
    {
        return $this->screen('admin_departments', [
            'title' => __('portal.admin_departments_title'),
            'eyebrow' => __('portal.admin_departments_eyebrow'),
            'description' => __('portal.admin_departments_description'),
            'primaryAction' => __('portal.add_department'),
            'stats' => [
                [__('portal.total_departments'), 8],
                [__('portal.department_managers'), 12],
                [__('portal.department_documents'), 148],
                [__('portal.training_items'), 34],
            ],
            'columns' => [__('portal.department'), __('portal.manager'), __('portal.documents'), __('portal.visibility')],
            'rows' => [
                [__('portal.hospitality_management'), 'Head of Hospitality', '32', __('portal.private')],
                [__('portal.property_management'), 'Head of Property', '45', __('portal.private')],
                [__('portal.hr_shared_services'), 'HR Lead', '18', __('portal.private')],
                [__('portal.oud_academy'), 'Training Lead', '34', __('portal.private')],
            ],
            'cards' => [
                [__('portal.admin_departments_card_1_title'), __('portal.admin_departments_card_1_body')],
                [__('portal.admin_departments_card_2_title'), __('portal.admin_departments_card_2_body')],
            ],
            'tableTitle' => __('portal.departments_table'),
            'formTitle' => __('portal.department_form'),
            'formFields' => [
                ['label' => __('portal.department_name'), 'placeholder' => __('portal.department_name')],
                ['label' => __('portal.manager'), 'placeholder' => __('portal.department_manager_name')],
                ['label' => __('portal.visibility'), 'type' => 'select', 'options' => [__('portal.private'), __('portal.admin_only')]],
                ['label' => __('portal.document_library'), 'type' => 'select', 'options' => [__('portal.enabled'), __('portal.pending')]],
                ['label' => __('portal.description'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.department_description_placeholder')],
                ['label' => __('portal.enable_oud_academy_area'), 'type' => 'checkbox', 'wide' => true],
            ],
        ]);
    }

    public function properties(): View
    {
        return $this->screen('admin_properties', [
            'title' => __('portal.admin_properties_title'),
            'eyebrow' => __('portal.admin_properties_eyebrow'),
            'description' => __('portal.admin_properties_description'),
            'primaryAction' => __('portal.add_property'),
            'stats' => [
                [__('portal.total_properties'), 16],
                [__('portal.linked_landlords'), 24],
                [__('portal.property_photos'), 210],
                [__('portal.active_contracts'), 39],
            ],
            'columns' => [__('portal.property'), __('portal.landlords'), __('portal.reports'), __('portal.status')],
            'rows' => [
                ['OUD Square', '4', '12', __('portal.active')],
                ['OUD Dunes', '3', '8', __('portal.active')],
                ['OUD Reserve', '5', '16', __('portal.active')],
                ['La Perle by OUD', '2', '6', __('portal.active')],
            ],
            'cards' => [
                [__('portal.admin_properties_card_1_title'), __('portal.admin_properties_card_1_body')],
                [__('portal.admin_properties_card_2_title'), __('portal.admin_properties_card_2_body')],
            ],
            'tableTitle' => __('portal.properties_table'),
            'formTitle' => __('portal.property_form'),
            'formFields' => [
                ['label' => __('portal.property_name'), 'placeholder' => 'OUD Square'],
                ['label' => __('portal.property_location'), 'placeholder' => __('portal.property_location_placeholder')],
                ['label' => __('portal.linked_landlord_users'), 'placeholder' => __('portal.linked_landlord_users_placeholder')],
                ['label' => __('portal.property_status'), 'type' => 'select', 'options' => [__('portal.active'), __('portal.pending'), __('portal.archived')]],
                ['label' => __('portal.property_photo_upload'), 'type' => 'file', 'accept' => '.jpg,.jpeg,.png'],
                ['label' => __('portal.property_notes'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.property_notes_placeholder')],
            ],
        ]);
    }

    public function documents(): View
    {
        return $this->screen('admin_documents', [
            'title' => __('portal.admin_documents_title'),
            'eyebrow' => __('portal.admin_documents_eyebrow'),
            'description' => __('portal.admin_documents_description'),
            'primaryAction' => __('portal.upload_document'),
            'stats' => [
                [__('portal.total_documents'), 420],
                [__('portal.department_files'), 220],
                [__('portal.property_files'), 126],
                [__('portal.training_files'), 74],
            ],
            'columns' => [__('portal.file'), __('portal.category'), __('portal.access'), __('portal.updated')],
            'rows' => [
                ['Q3 Budget Pack.pdf', __('portal.reports'), __('portal.admin_only'), __('portal.today')],
                ['Property Photos.zip', __('portal.media'), __('portal.landlords'), __('portal.today')],
                ['HR Handbook.pdf', __('portal.documents'), __('portal.employees'), __('portal.this_week')],
                ['Academy Onboarding.mp4', __('portal.training_material'), __('portal.employees'), __('portal.this_week')],
            ],
            'cards' => [
                [__('portal.admin_documents_card_1_title'), __('portal.admin_documents_card_1_body')],
                [__('portal.admin_documents_card_2_title'), __('portal.admin_documents_card_2_body')],
            ],
            'tableTitle' => __('portal.documents_table'),
            'formTitle' => __('portal.document_upload_form'),
            'formFields' => [
                ['label' => __('portal.document_title'), 'placeholder' => __('portal.document_title')],
                ['label' => __('portal.category'), 'type' => 'select', 'options' => [__('portal.department_documents'), __('portal.property_documents'), __('portal.contracts'), __('portal.invoices'), __('portal.certificates')]],
                ['label' => __('portal.assigned_department'), 'type' => 'select', 'options' => $this->departmentOptions()],
                ['label' => __('portal.assigned_property'), 'placeholder' => __('portal.assigned_property_placeholder')],
                ['label' => __('portal.visibility'), 'type' => 'select', 'options' => [__('portal.admin_only'), __('portal.employees'), __('portal.landlords'), __('portal.selected_users')]],
                ['label' => __('portal.upload_file'), 'type' => 'file', 'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.mp4'],
                ['label' => __('portal.replace_existing_document'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    public function academy(): View
    {
        return $this->screen('admin_academy', [
            'title' => __('portal.admin_academy_title'),
            'eyebrow' => __('portal.admin_academy_eyebrow'),
            'description' => __('portal.admin_academy_description'),
            'primaryAction' => __('portal.upload_training'),
            'stats' => [
                [__('portal.training_files'), 74],
                [__('portal.training_videos'), 12],
                [__('portal.assigned_departments'), 8],
                [__('portal.storage_review'), __('portal.required')],
            ],
            'columns' => [__('portal.training_item'), __('portal.format'), __('portal.audience'), __('portal.updated')],
            'rows' => [
                [__('portal.onboarding_pack'), 'PDF', __('portal.all_departments'), __('portal.today')],
                [__('portal.hospitality_service_video'), 'MP4', __('portal.hospitality_management'), __('portal.this_week')],
                [__('portal.hr_policy_training'), 'PowerPoint', __('portal.hr_shared_services'), __('portal.this_week')],
                [__('portal.property_management_playbook'), 'Word', __('portal.property_management'), __('portal.this_week')],
            ],
            'cards' => [
                [__('portal.admin_academy_card_1_title'), __('portal.admin_academy_card_1_body')],
                [__('portal.admin_academy_card_2_title'), __('portal.admin_academy_card_2_body')],
            ],
            'tableTitle' => __('portal.training_library_table'),
            'formTitle' => __('portal.training_upload_form'),
            'formFields' => [
                ['label' => __('portal.training_title'), 'placeholder' => __('portal.training_title')],
                ['label' => __('portal.format'), 'type' => 'select', 'options' => ['PDF', 'Word', 'Excel', 'PowerPoint', 'MP4']],
                ['label' => __('portal.audience'), 'type' => 'select', 'options' => [__('portal.all_departments'), ...$this->departmentOptions()]],
                ['label' => __('portal.training_file'), 'type' => 'file', 'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4'],
                ['label' => __('portal.training_summary'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.training_summary_placeholder')],
                ['label' => __('portal.confirm_video_storage_review'), 'type' => 'checkbox', 'wide' => true],
            ],
        ]);
    }

    public function reports(): View
    {
        return $this->screen('admin_reports', [
            'title' => __('portal.admin_reports_title'),
            'eyebrow' => __('portal.admin_reports_eyebrow'),
            'description' => __('portal.admin_reports_description'),
            'primaryAction' => __('portal.create_report'),
            'stats' => [
                [__('portal.monthly_reports'), 18],
                [__('portal.pending_review'), 5],
                [__('portal.published_reports'), 73],
                [__('portal.permanent_records'), 73],
            ],
            'columns' => [__('portal.report'), __('portal.property'), __('portal.period'), __('portal.status')],
            'rows' => [
                ['Occupancy Summary', 'OUD Square', 'Jul 2026', __('portal.pending_review')],
                ['Revenue Report', 'OUD Dunes', 'Jul 2026', __('portal.pending_review')],
                ['Maintenance Report', 'OUD Reserve', 'Jun 2026', __('portal.published')],
                ['Market Report', 'La Perle by OUD', 'Jun 2026', __('portal.published')],
            ],
            'cards' => [
                [__('portal.admin_reports_card_1_title'), __('portal.admin_reports_card_1_body')],
                [__('portal.admin_reports_card_2_title'), __('portal.admin_reports_card_2_body')],
            ],
            'tableTitle' => __('portal.landlord_reports_table'),
            'formTitle' => __('portal.monthly_report_form'),
            'formFields' => [
                ['label' => __('portal.report_title'), 'placeholder' => __('portal.report_title')],
                ['label' => __('portal.property'), 'type' => 'select', 'options' => $this->propertyOptions()],
                ['label' => __('portal.report_period'), 'type' => 'month'],
                ['label' => __('portal.report_type'), 'type' => 'select', 'options' => [__('portal.pnl_report'), __('portal.budget_report'), __('portal.maintenance_report'), __('portal.market_report')]],
                ['label' => __('portal.occupancy_percentage'), 'type' => 'number', 'placeholder' => '86'],
                ['label' => __('portal.gross_revenue'), 'type' => 'number', 'placeholder' => '1250000'],
                ['label' => __('portal.net_revenue'), 'type' => 'number', 'placeholder' => '910000'],
                ['label' => __('portal.report_file'), 'type' => 'file', 'accept' => '.pdf,.xls,.xlsx'],
                ['label' => __('portal.submit_for_admin_approval'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    public function approvals(): View
    {
        return $this->screen('admin_approvals', [
            'title' => __('portal.admin_approvals_title'),
            'eyebrow' => __('portal.admin_approvals_eyebrow'),
            'description' => __('portal.admin_approvals_description'),
            'primaryAction' => __('portal.new_approval_request'),
            'stats' => [
                [__('portal.awaiting_landlord'), 9],
                [__('portal.approved_requests'), 28],
                [__('portal.rejected_requests'), 3],
                [__('portal.attachments'), 44],
            ],
            'columns' => [__('portal.request'), __('portal.property'), __('portal.landlord'), __('portal.status')],
            'rows' => [
                [__('portal.maintenance_request'), 'OUD Square', 'Abdullah Al Saud', __('portal.awaiting_landlord')],
                [__('portal.budget_approval'), 'OUD Dunes', 'Maha Al Rashid', __('portal.awaiting_landlord')],
                [__('portal.contract_review'), 'OUD Reserve', 'Faisal Al Omar', __('portal.approved')],
                [__('portal.discount_request'), 'La Perle by OUD', 'Noura Al Fahad', __('portal.rejected')],
            ],
            'cards' => [
                [__('portal.admin_approvals_card_1_title'), __('portal.admin_approvals_card_1_body')],
                [__('portal.admin_approvals_card_2_title'), __('portal.admin_approvals_card_2_body')],
            ],
            'tableTitle' => __('portal.approval_requests_table'),
            'formTitle' => __('portal.approval_request_form'),
            'formFields' => [
                ['label' => __('portal.request_title'), 'placeholder' => __('portal.request_title')],
                ['label' => __('portal.request_type'), 'type' => 'select', 'options' => [__('portal.budgets'), __('portal.maintenance_requests'), __('portal.contracts'), __('portal.discounts'), __('portal.grace_periods'), __('portal.special_events')]],
                ['label' => __('portal.property'), 'type' => 'select', 'options' => $this->propertyOptions()],
                ['label' => __('portal.landlord'), 'placeholder' => __('portal.landlord_name')],
                ['label' => __('portal.due_date'), 'type' => 'date'],
                ['label' => __('portal.supporting_documents'), 'type' => 'file', 'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png'],
                ['label' => __('portal.request_details'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.request_details_placeholder')],
            ],
        ]);
    }

    public function announcements(): View
    {
        return $this->screen('admin_announcements', [
            'title' => __('portal.admin_announcements_title'),
            'eyebrow' => __('portal.admin_announcements_eyebrow'),
            'description' => __('portal.admin_announcements_description'),
            'primaryAction' => __('portal.new_announcement'),
            'stats' => [
                [__('portal.sent_this_month'), 14],
                [__('portal.department_posts'), 8],
                [__('portal.property_posts'), 4],
                [__('portal.all_user_posts'), 2],
            ],
            'columns' => [__('portal.announcement'), __('portal.audience'), __('portal.author'), __('portal.status')],
            'rows' => [
                [__('portal.system_maintenance'), __('portal.all_users'), 'Admin', __('portal.scheduled')],
                [__('portal.new_training_files'), __('portal.employees'), 'Department Manager', __('portal.published')],
                [__('portal.monthly_report_notice'), __('portal.landlords'), 'Admin', __('portal.published')],
                [__('portal.hr_policy_update'), __('portal.hr_shared_services'), 'HR Lead', __('portal.draft')],
            ],
            'cards' => [
                [__('portal.admin_announcements_card_1_title'), __('portal.admin_announcements_card_1_body')],
                [__('portal.admin_announcements_card_2_title'), __('portal.admin_announcements_card_2_body')],
            ],
            'tableTitle' => __('portal.announcements_table'),
            'formTitle' => __('portal.announcement_form'),
            'formFields' => [
                ['label' => __('portal.announcement_title'), 'placeholder' => __('portal.announcement_title')],
                ['label' => __('portal.audience'), 'type' => 'select', 'options' => [__('portal.all_users'), __('portal.department'), __('portal.property'), __('portal.specific_users')]],
                ['label' => __('portal.assigned_department'), 'type' => 'select', 'options' => $this->departmentOptions()],
                ['label' => __('portal.assigned_property'), 'placeholder' => __('portal.assigned_property_placeholder')],
                ['label' => __('portal.publish_date'), 'type' => 'date'],
                ['label' => __('portal.announcement_message'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.announcement_message_placeholder')],
                ['label' => __('portal.send_email_notification'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    public function notifications(): View
    {
        return $this->screen('admin_notifications', [
            'title' => __('portal.admin_notifications_title'),
            'eyebrow' => __('portal.admin_notifications_eyebrow'),
            'description' => __('portal.admin_notifications_description'),
            'primaryAction' => __('portal.configure_notification'),
            'stats' => [
                [__('portal.email_rules'), 3],
                [__('portal.active_channels'), 2],
                [__('portal.queued_messages'), 11],
                [__('portal.failed_messages'), 0],
            ],
            'columns' => [__('portal.trigger'), __('portal.recipient_group'), __('portal.channel'), __('portal.status')],
            'rows' => [
                [__('portal.department_document_posted'), __('portal.employees'), __('portal.email_and_portal'), __('portal.active')],
                [__('portal.landlord_report_posted'), __('portal.landlords'), __('portal.email_and_portal'), __('portal.active')],
                [__('portal.approval_request_posted'), __('portal.landlords'), __('portal.email_and_portal'), __('portal.active')],
                [__('portal.password_reset_requested'), __('portal.users'), __('portal.email'), __('portal.active')],
            ],
            'cards' => [
                [__('portal.admin_notifications_card_1_title'), __('portal.admin_notifications_card_1_body')],
                [__('portal.admin_notifications_card_2_title'), __('portal.admin_notifications_card_2_body')],
            ],
            'tableTitle' => __('portal.notification_rules_table'),
            'formTitle' => __('portal.notification_rule_form'),
            'formFields' => [
                ['label' => __('portal.trigger'), 'type' => 'select', 'options' => [__('portal.department_document_posted'), __('portal.landlord_report_posted'), __('portal.approval_request_posted'), __('portal.password_reset_requested')]],
                ['label' => __('portal.recipient_group'), 'type' => 'select', 'options' => [__('portal.employees'), __('portal.landlords'), __('portal.department_manager'), __('portal.admin')]],
                ['label' => __('portal.channel'), 'type' => 'select', 'options' => [__('portal.email'), __('portal.portal'), __('portal.email_and_portal')]],
                ['label' => __('portal.email_subject'), 'placeholder' => __('portal.email_subject')],
                ['label' => __('portal.message_template'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.message_template_placeholder')],
                ['label' => __('portal.enable_notification_rule'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    public function auditLogs(): View
    {
        return $this->screen('admin_audit', [
            'title' => __('portal.admin_audit_title'),
            'eyebrow' => __('portal.admin_audit_eyebrow'),
            'description' => __('portal.admin_audit_description'),
            'primaryAction' => __('portal.export_logs'),
            'stats' => [
                [__('portal.tracked_events'), 186],
                [__('portal.password_resets'), 7],
                [__('portal.upload_events'), 53],
                [__('portal.permission_changes'), 11],
            ],
            'columns' => [__('portal.event'), __('portal.user'), __('portal.area'), __('portal.time')],
            'rows' => [
                [__('portal.user_login'), 'OUD Admin', __('portal.authentication'), __('portal.now')],
                [__('portal.document_uploaded'), 'Property Manager', __('portal.documents'), __('portal.today')],
                [__('portal.report_approved'), 'Admin', __('portal.reports'), __('portal.today')],
                [__('portal.permission_updated'), 'Admin', __('portal.users'), __('portal.this_week')],
            ],
            'cards' => [
                [__('portal.admin_audit_card_1_title'), __('portal.admin_audit_card_1_body')],
                [__('portal.admin_audit_card_2_title'), __('portal.admin_audit_card_2_body')],
            ],
            'tableTitle' => __('portal.audit_logs_table'),
            'formTitle' => __('portal.audit_filter_form'),
            'formFields' => [
                ['label' => __('portal.event_type'), 'type' => 'select', 'options' => [__('portal.user_creation'), __('portal.password_resets'), __('portal.file_uploads'), __('portal.report_approvals'), __('portal.permission_changes'), __('portal.landlord_decisions')]],
                ['label' => __('portal.user'), 'placeholder' => __('portal.user_name_or_email')],
                ['label' => __('portal.area'), 'type' => 'select', 'options' => [__('portal.authentication'), __('portal.users'), __('portal.documents'), __('portal.reports'), __('portal.approvals')]],
                ['label' => __('portal.date_from'), 'type' => 'date'],
                ['label' => __('portal.date_to'), 'type' => 'date'],
                ['label' => __('portal.export_format'), 'type' => 'select', 'options' => ['CSV', 'Excel', 'PDF']],
            ],
        ]);
    }

    public function integrations(): View
    {
        return $this->screen('admin_integrations', [
            'title' => __('portal.admin_integrations_title'),
            'eyebrow' => __('portal.admin_integrations_eyebrow'),
            'description' => __('portal.admin_integrations_description'),
            'primaryAction' => __('portal.review_integration'),
            'stats' => [
                [__('portal.odoo_modules'), 4],
                [__('portal.sync_direction'), __('portal.to_be_confirmed')],
                [__('portal.api_credentials'), __('portal.not_connected')],
                [__('portal.data_mapping'), __('portal.draft')],
            ],
            'columns' => [__('portal.integration_area'), __('portal.source'), __('portal.destination'), __('portal.status')],
            'rows' => [
                [__('portal.property_records'), 'Odoo', __('portal.portal'), __('portal.pending')],
                [__('portal.landlord_contacts'), 'Odoo', __('portal.portal'), __('portal.pending')],
                [__('portal.financial_reports'), 'Odoo', __('portal.reports'), __('portal.to_be_confirmed')],
                [__('portal.document_links'), __('portal.portal'), 'Odoo', __('portal.to_be_confirmed')],
            ],
            'cards' => [
                [__('portal.admin_integrations_card_1_title'), __('portal.admin_integrations_card_1_body')],
                [__('portal.admin_integrations_card_2_title'), __('portal.admin_integrations_card_2_body')],
            ],
            'tableTitle' => __('portal.odoo_integration_table'),
            'formTitle' => __('portal.odoo_integration_form'),
            'formFields' => [
                ['label' => __('portal.integration_area'), 'type' => 'select', 'options' => [__('portal.properties'), __('portal.tenants'), __('portal.financial_figures'), __('portal.reports'), __('portal.approval_workflows'), __('portal.user_department_data')]],
                ['label' => __('portal.source'), 'type' => 'select', 'options' => ['Odoo', __('portal.portal')]],
                ['label' => __('portal.destination'), 'type' => 'select', 'options' => [__('portal.portal'), 'Odoo']],
                ['label' => __('portal.sync_direction'), 'type' => 'select', 'options' => [__('portal.one_way_sync'), __('portal.two_way_sync'), __('portal.to_be_confirmed')]],
                ['label' => __('portal.api_endpoint'), 'type' => 'url', 'placeholder' => 'https://odoo.example.com'],
                ['label' => __('portal.integration_notes'), 'type' => 'textarea', 'wide' => true, 'placeholder' => __('portal.integration_notes_placeholder')],
            ],
        ]);
    }

    public function settings(): View
    {
        return $this->screen('admin_settings', [
            'title' => __('portal.admin_settings_title'),
            'eyebrow' => __('portal.admin_settings_eyebrow'),
            'description' => __('portal.admin_settings_description'),
            'primaryAction' => __('portal.save_settings'),
            'stats' => [
                [__('portal.locale_options'), 2],
                [__('portal.session_security'), __('portal.enabled')],
                [__('portal.email_notifications'), __('portal.ready')],
                [__('portal.odoo_integration'), __('portal.pending')],
            ],
            'columns' => [__('portal.setting'), __('portal.current_value'), __('portal.owner'), __('portal.status')],
            'rows' => [
                [__('portal.production_https'), __('portal.required'), 'Admin', __('portal.active')],
                [__('portal.default_language'), 'English / العربية', 'Admin', __('portal.active')],
                [__('portal.password_policy'), __('portal.eight_characters'), 'Security', __('portal.active')],
                [__('portal.odoo_sync'), __('portal.awaiting_details'), 'Integration', __('portal.pending')],
            ],
            'cards' => [
                [__('portal.admin_settings_card_1_title'), __('portal.admin_settings_card_1_body')],
                [__('portal.admin_settings_card_2_title'), __('portal.admin_settings_card_2_body')],
            ],
            'tableTitle' => __('portal.system_settings_table'),
            'formTitle' => __('portal.system_settings_form'),
            'formFields' => [
                ['label' => __('portal.default_language'), 'type' => 'select', 'options' => ['English', 'العربية']],
                ['label' => __('portal.timezone'), 'type' => 'select', 'options' => ['Asia/Riyadh', 'Asia/Karachi', 'UTC']],
                ['label' => __('portal.session_lifetime'), 'type' => 'number', 'placeholder' => '120'],
                ['label' => __('portal.password_policy'), 'type' => 'select', 'options' => [__('portal.eight_characters'), __('portal.strong_password')]],
                ['label' => __('portal.require_https'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
                ['label' => __('portal.enable_audit_logging'), 'type' => 'checkbox', 'wide' => true, 'checked' => true],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function screen(string $section, array $data): View
    {
        $view = match ($section) {
            'admin_users' => 'admin.users.view-users',
            'admin_permissions' => 'admin.permissions.view-permissions',
            'admin_departments' => 'admin.departments.view-departments',
            'admin_properties' => 'admin.properties.view-properties',
            'admin_documents' => 'admin.documents.view-documents',
            'admin_academy' => 'admin.academy.view-training',
            'admin_reports' => 'admin.reports.view-reports',
            'admin_approvals' => 'admin.approvals.view-approvals',
            'admin_announcements' => 'admin.announcements.view-announcements',
            'admin_notifications' => 'admin.notifications.view-notifications',
            'admin_audit' => 'admin.audit.view-audit-logs',
            'admin_integrations' => 'admin.integrations.view-integrations',
            'admin_settings' => 'admin.settings.view-settings',
        };

        return view($view, [
            ...$data,
            'section' => $section,
            'filters' => $data['filters'] ?? [
                __('portal.all_records'),
                __('portal.active'),
                __('portal.pending'),
                __('portal.archived'),
            ],
            'quickActions' => $data['quickActions'] ?? [
                __('portal.review_visibility'),
                __('portal.check_permissions'),
                __('portal.export_current_view'),
            ],
            'workflow' => $data['workflow'] ?? [
                [__('portal.secure_access'), __('portal.workflow_secure_access')],
                [__('portal.visibility_review'), __('portal.workflow_visibility_review')],
                [__('portal.audit_ready'), __('portal.workflow_audit_ready')],
            ],
            'formFields' => $data['formFields'] ?? [],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function departmentOptions(): array
    {
        return [
            __('portal.hospitality_management'),
            __('portal.property_management'),
            __('portal.hr_shared_services'),
            __('portal.investment_management'),
            __('portal.development_management'),
            __('portal.commercial_division'),
            __('portal.accountant_management'),
            __('portal.oud_academy'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function propertyOptions(): array
    {
        return ['OUD Square', 'OUD Dunes', 'OUD Reserve', 'La Perle by OUD'];
    }

    private function formView(string $module): string
    {
        return match ($module) {
            'users' => 'admin.users.create-user',
            'permissions' => 'admin.permissions.create-permission',
            'departments' => 'admin.departments.create-department',
            'properties' => 'admin.properties.create-property',
            'documents' => 'admin.documents.upload-document',
            'academy' => 'admin.academy.upload-training',
            'reports' => 'admin.reports.create-report',
            'approvals' => 'admin.approvals.create-approval',
            'announcements' => 'admin.announcements.create-announcement',
            'notifications' => 'admin.notifications.create-notification',
            'integrations' => 'admin.integrations.create-integration',
            'settings' => 'admin.settings.update-settings',
        };
    }

    /**
     * @return array<int, string>
     */
    private function formModules(): array
    {
        return [
            'users',
            'permissions',
            'departments',
            'properties',
            'documents',
            'academy',
            'reports',
            'approvals',
            'announcements',
            'notifications',
            'integrations',
            'settings',
        ];
    }
}
