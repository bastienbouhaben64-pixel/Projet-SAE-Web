<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilEntreprise;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    /** Liste des entreprises avec leur statut (compte actif + fiche validée). */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $filtre = $request->query('filtre', 'a_valider');

        $base = Utilisateur::where('role', Utilisateur::ROLE_ENTREPRISE)
            ->with('profilEntreprise')
            ->orderBy('created_at', 'desc');

        if ($q) {
            $base->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhereHas('profilEntreprise', fn ($p) => $p->where('raison_sociale', 'like', "%{$q}%")->orWhere('siret', 'like', "%{$q}%"));
            });
        }

        if ($filtre === 'a_valider') {
            $base->where(function ($w) {
                $w->where('is_active', false)
                  ->orWhereDoesntHave('profilEntreprise')
                  ->orWhereHas('profilEntreprise', fn ($p) => $p->where('is_validated', false));
            });
        } elseif ($filtre === 'validees') {
            $base->where('is_active', true)
                ->whereHas('profilEntreprise', fn ($p) => $p->where('is_validated', true));
        }

        $entreprises = $base->paginate(20)->withQueryString();

        $compteurs = [
            'a_valider' => Utilisateur::where('role', Utilisateur::ROLE_ENTREPRISE)
                ->where(function ($w) {
                    $w->where('is_active', false)
                      ->orWhereDoesntHave('profilEntreprise')
                      ->orWhereHas('profilEntreprise', fn ($p) => $p->where('is_validated', false));
                })->count(),
            'validees' => Utilisateur::where('role', Utilisateur::ROLE_ENTREPRISE)
                ->where('is_active', true)
                ->whereHas('profilEntreprise', fn ($p) => $p->where('is_validated', true))
                ->count(),
            'toutes' => Utilisateur::where('role', Utilisateur::ROLE_ENTREPRISE)->count(),
        ];

        return view('admin.entreprises.index', compact('entreprises', 'q', 'filtre', 'compteurs'));
    }

    /** Valide la fiche entreprise (active le compte + flag is_validated). */
    public function validate(Utilisateur $utilisateur, Request $request)
    {
        abort_unless($utilisateur->isEntreprise(), 404);
        $profil = ProfilEntreprise::firstOrNew(['user_id' => $utilisateur->id]);
        abort_if(! $profil->exists, 422, "Cette entreprise n'a pas encore renseigné sa fiche.");

        $utilisateur->update(['is_active' => true]);
        $profil->update(['is_validated' => true]);

        Notify::send($utilisateur, 'compte.valide', 'Compte entreprise validé', 'Vous pouvez désormais publier des offres.', route('offres.miennes'));
        ActivityLogger::log('admin.entreprise.validee', ['user_id' => $utilisateur->id]);

        return back()->with('status', "Fiche entreprise validée pour {$utilisateur->name}.");
    }

    /** Rejette / désactive l'entreprise. */
    public function reject(Utilisateur $utilisateur, Request $request)
    {
        abort_unless($utilisateur->isEntreprise(), 404);

        $data = $request->validate([
            'motif' => ['nullable', 'string', 'max:500'],
        ]);

        $utilisateur->update(['is_active' => false]);
        if ($utilisateur->profilEntreprise) {
            $utilisateur->profilEntreprise->update(['is_validated' => false]);
        }

        Notify::send($utilisateur, 'compte.rejete', 'Compte entreprise suspendu', $data['motif'] ?? 'Votre compte a été suspendu par un administrateur.');
        ActivityLogger::log('admin.entreprise.rejetee', ['user_id' => $utilisateur->id, 'motif' => $data['motif'] ?? null]);

        return back()->with('status', "Compte entreprise suspendu pour {$utilisateur->name}.");
    }
}
