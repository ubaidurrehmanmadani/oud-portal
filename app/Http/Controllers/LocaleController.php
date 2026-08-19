<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        validator(['locale' => $locale], [
            'locale' => ['required', Rule::in(['en', 'ar'])],
        ])->validate();

        $request->session()->put('locale', $locale);

        return back();
    }
}
