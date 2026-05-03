<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\Parametres;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    public function edit()
    {
        return view('admin.parametres.edit', [
            'email2faEnabled' => Parametres::email2faEnabled(),
        ]);
    }

    public function update(Request $request)
    {
        // Un champ absent ou à 0 signifie que l'A2F email est désactivée.
        $data = $request->validate([
            'email_2fa_enabled' => ['nullable', 'boolean'],
        ]);

        $enabled = (bool) ($data['email_2fa_enabled'] ?? false);
        Parametres::set('email_2fa_enabled', $enabled);

        ActivityLogger::log('admin.parametres.updated', [
            'email_2fa_enabled' => $enabled,
        ]);

        return back()->with('status', $enabled
            ? "L'authentification à deux facteurs par email est activée."
            : "L'authentification à deux facteurs par email est désactivée.");
    }
}
