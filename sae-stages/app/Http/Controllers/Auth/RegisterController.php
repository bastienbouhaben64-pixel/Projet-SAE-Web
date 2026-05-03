<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProfilEntreprise;
use App\Models\Formation;
use App\Models\ProfilEtudiant;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show(Request $request)
    {
        $type = $request->query('type', 'etudiant');
        if (! in_array($type, ['etudiant', 'entreprise'], true)) {
            $type = 'etudiant';
        }
        $formations = Formation::where('is_active', true)->orderBy('intitule')->get();
        return view('auth.register', compact('type', 'formations'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type');
        abort_unless(in_array($type, ['etudiant', 'entreprise'], true), 422);

        $base = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('utilisateurs', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($type === 'etudiant') {
            $extra = $request->validate([
                'formation_id' => ['nullable', Rule::exists('formations', 'id')],
                'promo' => ['nullable', 'string', 'max:50'],
                'telephone' => ['nullable', 'string', 'max:30'],
            ]);
        } else {
            $extra = $request->validate([
                'raison_sociale' => ['required', 'string', 'max:255'],
                'siret' => ['nullable', 'string', 'max:20'],
                'secteur' => ['nullable', 'string', 'max:120'],
                'site_web' => ['nullable', 'url', 'max:255'],
                'adresse' => ['nullable', 'string', 'max:255'],
            ]);
        }

        $utilisateur = DB::transaction(function () use ($base, $type, $extra) {
            $utilisateur = Utilisateur::create([
                'name' => $base['name'],
                'email' => $base['email'],
                'password' => Hash::make($base['password']),
                'role' => $type === 'etudiant' ? Utilisateur::ROLE_ETUDIANT : Utilisateur::ROLE_ENTREPRISE,
                'is_active' => false,
            ]);

            if ($type === 'etudiant') {
                ProfilEtudiant::create([
                    'user_id' => $utilisateur->id,
                    'formation_id' => $extra['formation_id'] ?? null,
                    'promo' => $extra['promo'] ?? null,
                    'telephone' => $extra['telephone'] ?? null,
                ]);
            } else {
                ProfilEntreprise::create([
                    'user_id' => $utilisateur->id,
                    'raison_sociale' => $extra['raison_sociale'],
                    'siret' => $extra['siret'] ?? null,
                    'secteur' => $extra['secteur'] ?? null,
                    'site_web' => $extra['site_web'] ?? null,
                    'adresse' => $extra['adresse'] ?? null,
                    'is_validated' => false,
                ]);
            }

            return $utilisateur;
        });

        ActivityLogger::log('user.registered', ['role' => $utilisateur->role], $utilisateur->id);

        return redirect()->route('connexion')
            ->with('status', 'Compte créé. Un administrateur doit l\'activer avant la première connexion.');
    }
}
