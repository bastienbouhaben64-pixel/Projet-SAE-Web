<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $utilisateurs = Utilisateur::query()
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) =>
                $w->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")))
            ->when($request->role, fn ($q, $v) => $q->where('role', $v))
            ->when($request->status === 'pending', fn ($q) => $q->where('is_active', false))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.utilisateurs.index', compact('utilisateurs'));
    }

    public function update( Utilisateur $utilisateur, Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(Utilisateur::ROLES)],
            'is_active' => ['required', 'boolean'],
        ]);
        $utilisateur->update($data);

        // sync company validation
        if ($utilisateur->isEntreprise() && $utilisateur->companyProfile) {
            $utilisateur->companyProfile->update(['is_validated' => $utilisateur->is_active]);
        }

        ActivityLogger::log('admin.user.updated', ['user_id' => $utilisateur->id, 'changes' => $data]);
        return back()->with('status', 'Utilisateur mis à jour.');
    }
}
