<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->dashboardRouteName())
        : redirect()->route('login');
});

Route::post('/language/{locale}', [LocaleController::class, 'update'])->name('language.update');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/sign-up', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/sign-up', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    Route::get('/dashboard/department-manager', [DashboardController::class, 'manager'])->name('dashboard.manager');
    Route::get('/dashboard/employee', [DashboardController::class, 'employee'])->name('dashboard.employee');
    Route::get('/dashboard/landlord', [DashboardController::class, 'landlord'])->name('dashboard.landlord');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/dashboard/admin')->name('home');
        Route::redirect('/dashboard', '/dashboard/admin')->name('dashboard');
        Route::redirect('/users', '/admin/users/view-users');
        Route::redirect('/permissions', '/admin/permissions/view-permissions');
        Route::redirect('/departments', '/admin/departments/view-departments');
        Route::redirect('/properties', '/admin/properties/view-properties');
        Route::redirect('/documents', '/admin/documents/view-documents');
        Route::redirect('/academy', '/admin/academy/view-training');
        Route::redirect('/reports', '/admin/reports/view-reports');
        Route::redirect('/approvals', '/admin/approvals/view-approvals');
        Route::redirect('/announcements', '/admin/announcements/view-announcements');
        Route::redirect('/notifications', '/admin/notifications/view-notifications');
        Route::redirect('/audit-logs', '/admin/audit-logs/view-audit-logs');
        Route::redirect('/integrations', '/admin/integrations/view-integrations');
        Route::redirect('/settings', '/admin/settings/view-settings');

        Route::get('/users/view-users', [AdminController::class, 'users'])->name('users.view');
        Route::get('/users/create-user', [AdminController::class, 'create'])->defaults('module', 'users')->name('users.create');
        Route::get('/permissions/view-permissions', [AdminController::class, 'permissions'])->name('permissions.view');
        Route::get('/permissions/create-permission', [AdminController::class, 'create'])->defaults('module', 'permissions')->name('permissions.create');
        Route::get('/departments/view-departments', [AdminController::class, 'departments'])->name('departments.view');
        Route::get('/departments/create-department', [AdminController::class, 'create'])->defaults('module', 'departments')->name('departments.create');
        Route::get('/properties/view-properties', [AdminController::class, 'properties'])->name('properties.view');
        Route::get('/properties/create-property', [AdminController::class, 'create'])->defaults('module', 'properties')->name('properties.create');
        Route::get('/documents/view-documents', [AdminController::class, 'documents'])->name('documents.view');
        Route::get('/documents/upload-document', [AdminController::class, 'create'])->defaults('module', 'documents')->name('documents.upload');
        Route::get('/academy/view-training', [AdminController::class, 'academy'])->name('academy.view');
        Route::get('/academy/upload-training', [AdminController::class, 'create'])->defaults('module', 'academy')->name('academy.upload');
        Route::get('/reports/view-reports', [AdminController::class, 'reports'])->name('reports.view');
        Route::get('/reports/create-report', [AdminController::class, 'create'])->defaults('module', 'reports')->name('reports.create');
        Route::get('/approvals/view-approvals', [AdminController::class, 'approvals'])->name('approvals.view');
        Route::get('/approvals/create-approval', [AdminController::class, 'create'])->defaults('module', 'approvals')->name('approvals.create');
        Route::get('/announcements/view-announcements', [AdminController::class, 'announcements'])->name('announcements.view');
        Route::get('/announcements/create-announcement', [AdminController::class, 'create'])->defaults('module', 'announcements')->name('announcements.create');
        Route::get('/notifications/view-notifications', [AdminController::class, 'notifications'])->name('notifications.view');
        Route::get('/notifications/create-notification', [AdminController::class, 'create'])->defaults('module', 'notifications')->name('notifications.create');
        Route::get('/audit-logs/view-audit-logs', [AdminController::class, 'auditLogs'])->name('audit.view');
        Route::get('/integrations/view-integrations', [AdminController::class, 'integrations'])->name('integrations.view');
        Route::get('/integrations/create-integration', [AdminController::class, 'create'])->defaults('module', 'integrations')->name('integrations.create');
        Route::get('/settings/view-settings', [AdminController::class, 'settings'])->name('settings.view');
        Route::get('/settings/update-settings', [AdminController::class, 'create'])->defaults('module', 'settings')->name('settings.update');
    });
});
