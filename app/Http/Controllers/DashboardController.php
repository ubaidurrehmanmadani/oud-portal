<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboards.role', [
            'title' => 'Admin Dashboard',
            'eyebrow' => 'Full portal control',
            'items' => ['User management', 'Department setup', 'Property records', 'Report approval queue'],
        ]);
    }

    public function manager(): View
    {
        return view('dashboards.role', [
            'title' => 'Department Manager Dashboard',
            'eyebrow' => 'Department workspace',
            'items' => ['Department documents', 'Training material', 'Team announcements', 'Upload approvals'],
        ]);
    }

    public function employee(): View
    {
        return view('dashboards.role', [
            'title' => 'Employee Dashboard',
            'eyebrow' => 'Staff portal',
            'items' => ['Recent documents', 'Department announcements', 'Oud Academy files', 'Document search'],
        ]);
    }

    public function landlord(): View
    {
        return view('dashboards.role', [
            'title' => 'Landlord Dashboard',
            'eyebrow' => 'Property owner portal',
            'items' => ['Assigned properties', 'Performance KPIs', 'Reports and contracts', 'Approval requests'],
        ]);
    }
}
