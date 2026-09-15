<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\Department;
use App\Models\Property;
use App\Models\ReportSubmission;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'session.driver' => 'array', 'cache.default' => 'array', 'demo-access.enabled' => false]);
DB::purge('sqlite');
$pages = [];
Artisan::call('migrate', ['--force' => true]);
$admin = User::create(['name' => 'Portal Administrator', 'email' => 'manual@example.com', 'password' => Str::random(40), 'role' => 'admin']);
$employee = User::create(['name' => 'Example Employee', 'email' => 'employee@example.com', 'password' => Str::random(40), 'role' => 'employee']);
$department = Department::create(['name' => 'Property Management']);
$property = Property::create(['name' => 'Example Property']);
$manager = User::create(['name' => 'Example Manager', 'email' => 'manager@example.com', 'password' => Str::random(40), 'role' => 'department_manager', 'department_id' => $department->id]);
$manager->forceFill(['can_submit_financial_reports' => true])->save();
$manager->properties()->attach($property);
ReportSubmission::create(['user_id' => $manager->id, 'department_id' => $department->id, 'property_id' => $property->id, 'title' => 'Example monthly report', 'report_month' => '2026-09-01', 'status' => 'pending', 'submitted_at' => now(), 'file_path' => 'report-submissions/manual-example.pdf', 'file_name' => 'example.pdf', 'metrics' => ['occupancy' => 80, 'gross_revenue' => 50000, 'net_revenue' => 40000, 'rent' => 30000]]);
Auth::login($admin);
view()->share('errors', new ViewErrorBag);
foreach (['en', 'ar'] as $locale) {
    app()->setLocale($locale);
    foreach (['login', '/admin/users/account-requests', '/admin/departments/view-departments', '/admin/properties/view-properties', '/accounts/user/'.$employee->id.'/edit', '/manager/reports', '/manager/reports/upload', '/admin/report-reviews'] as $path) {
        $actor = str_starts_with($path, '/manager/') ? $manager : $admin;
        Auth::login($actor);
        $request = Request::create('/'.ltrim($path, '/'));
        $request->setLaravelSession(app('session')->driver());
        $request->setUserResolver(fn () => $actor);
        app()->instance('request', $request);
        if ($path === 'login') {
            $response = app(AuthenticatedSessionController::class)->create();
        } else {
            $route = app('router')->getRoutes()->match($request);
            $request->setRouteResolver(fn () => $route);
            $route->bind($request);
            $response = $route->run();
        }
        $html = $response->render();
        $html = preg_replace('~https?://localhost(?::[0-9]+)?/~', 'file://'.public_path().'/', $html);
        $html = preg_replace('~<script\b[^>]*>.*?</script>~is', '', $html);
        $html = preg_replace('~<link[^>]+href=[\"\']https://[^>]+>~i', '', $html);
        $pages[$locale.'-'.basename($path)] = $html;
    }
}

echo json_encode($pages, JSON_THROW_ON_ERROR);
