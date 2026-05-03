<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\ProfilEntreprise;
use App\Models\Stage;
use App\Models\Utilisateur;

class AccueilController extends Controller
{
    public function show()
    {
        $stats = [
            'offres' => Offre::published()->count(),
            'entreprises' => ProfilEntreprise::where('is_validated', true)->count(),
            'stages_en_cours' => Stage::where('status', Stage::STATUS_EN_COURS)->count(),
            'etudiants' => Utilisateur::where('role', Utilisateur::ROLE_ETUDIANT)->where('is_active', true)->count(),
        ];

        $offresRecentes = Offre::with(['company.companyProfile', 'formation'])
            ->published()
            ->latest()
            ->take(3)
            ->get();

        return view('welcome', compact('stats', 'offresRecentes'));
    }
}
