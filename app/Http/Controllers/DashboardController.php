<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboards.role', [
            'title' => __('portal.admin_dashboard'),
            'eyebrow' => __('portal.admin_eyebrow'),
            'items' => [
                __('portal.user_management'),
                __('portal.department_setup'),
                __('portal.property_records'),
                __('portal.report_approval_queue'),
            ],
        ]);
    }

    public function manager(): View
    {
        return view('dashboards.role', [
            'title' => __('portal.manager_dashboard'),
            'eyebrow' => __('portal.manager_eyebrow'),
            'items' => [
                __('portal.department_documents'),
                __('portal.training_material'),
                __('portal.team_announcements'),
                __('portal.upload_approvals'),
            ],
        ]);
    }

    public function employee(): View
    {
        return view('dashboards.role', [
            'title' => __('portal.employee_dashboard'),
            'eyebrow' => __('portal.employee_eyebrow'),
            'items' => [
                __('portal.recent_documents'),
                __('portal.department_announcements'),
                __('portal.oud_academy_files'),
                __('portal.document_search'),
            ],
        ]);
    }

    public function landlord(): View
    {
        return view('dashboards.role', [
            'title' => __('portal.landlord_dashboard'),
            'eyebrow' => __('portal.landlord_eyebrow'),
            'items' => [
                __('portal.assigned_properties'),
                __('portal.performance_kpis'),
                __('portal.reports_and_contracts'),
                __('portal.approval_requests'),
            ],
        ]);
    }
}
