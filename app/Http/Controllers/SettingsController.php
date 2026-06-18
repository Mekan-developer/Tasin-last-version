<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Settings/Edit', [
            'settings' => Setting::all(),
        ]);
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        $rules = match ($tab) {
            'general'  => [
                'market_name' => 'required|string|max:255',
                'phone'       => 'nullable|string|max:50',
                'address'     => 'nullable|string|max:500',
            ],
            'pricing'  => [
                'markup'           => 'required|numeric|min:0|max:100',
                'default_currency' => 'required|in:USD,TMT',
                'dual_currency'    => 'boolean',
                'auto_tmt'         => 'boolean',
                'round_tmt'        => 'boolean',
            ],
            'currency' => [
                'tmt_rate' => 'required|numeric|min:0.01',
            ],
            'display'  => [
                'show_views'     => 'boolean',
                'show_new_badge' => 'boolean',
                'show_cat_icons' => 'boolean',
                'enable_slider'  => 'boolean',
                'show_inactive'  => 'boolean',
            ],
            default => abort(422, 'Неверная вкладка'),
        };

        $data = $request->validate($rules);
        Setting::setMany($data);
        return back()->with('success', 'Настройки сохранены.');
    }
}
