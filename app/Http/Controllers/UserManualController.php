<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;

class UserManualController extends Controller
{
    public function index(Request $request)
    {
        $isLandlord = $request->user()->role === UserRole::LANDLORD;

        return view('workspace.manuals', [
            'title' => __('manual.title'), 'section' => 'manuals',
            'isLandlord' => $isLandlord,
            'properties' => $isLandlord ? $request->user()->properties()->orderBy('name')->get() : collect(),
            'selectedProperty' => null,
        ]);
    }

    public function pdf(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['en', 'ar'], true), 404);
        $path = base_path('docs/manuals/user-manual-'.$locale.'.pdf');
        abort_unless(is_file($path), 404);
        $headers = ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];

        return $request->boolean('download')
            ? response()->download($path, 'oud-user-manual-'.$locale.'.pdf', $headers)
            : response()->file($path, $headers + ['Content-Disposition' => 'inline; filename="oud-user-manual-'.$locale.'.pdf"']);
    }
}
