<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Convention;
use App\Models\Offre;
use App\Models\ProfilEntreprise;
use App\Models\Stage;
use App\Models\Utilisateur;
use App\Models\DemandeFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
    public function index(Request $request)
    {
        $utilisateur = $request->user();

        return match ($utilisateur->role) {
            Utilisateur::ROLE_ADMIN => view('tableaux_bord.admin', $this->statsAdmin()),
            Utilisateur::ROLE_ETUDIANT => view('tableaux_bord.etudiant', [
                'offresRecentes' => Offre::published()->latest()->take(5)->get(),
            ]),
            Utilisateur::ROLE_ENTREPRISE => view('tableaux_bord.entreprise', [
                'mesOffres' => $utilisateur->offers()->latest()->take(5)->get(),
                'totalOffres' => $utilisateur->offers()->count(),
            ]),
            Utilisateur::ROLE_PROFESSEUR => view('tableaux_bord.professeur', [
                'offresRecentes' => Offre::published()->latest()->take(5)->get(),
            ]),
            Utilisateur::ROLE_JURY => view('tableaux_bord.jury'),
            default => abort(403),
        };
    }

    private function statsAdmin(): array
    {
        $statutsStage = ['brouillon','convention','en_cours','termine','valide'];
        $parStatut = Stage::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status');
        $stagesParStatut = collect($statutsStage)->mapWithKeys(fn ($s) => [$s => (int) ($parStatut[$s] ?? 0)]);

        // Conventions : nb signées totalement vs en attente
        $signeesCompletes = Convention::whereNotNull('signed_student_at')
            ->whereNotNull('signed_company_at')
            ->whereNotNull('signed_tutor_at')
            ->whereNotNull('validated_admin_at')
            ->count();
        $conventionsTotal = Convention::count();

        $offresParStatut = Offre::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status');

        // Top 5 formations par nombre d'étudiants ayant un stage
        $topFormations = DB::table('stages')
            ->join('utilisateurs', 'utilisateurs.id', '=', 'stages.student_id')
            ->join('profils_etudiants', 'profils_etudiants.user_id', '=', 'utilisateurs.id')
            ->join('formations', 'formations.id', '=', 'profils_etudiants.formation_id')
            ->select('formations.intitule', DB::raw('count(*) as n'))
            ->groupBy('formations.intitule')
            ->orderByDesc('n')
            ->limit(5)
            ->get();

        // Stages créés sur les 6 derniers mois
        $debut = now()->copy()->startOfMonth()->subMonths(5);
        $serieMensuelle = collect(range(0, 5))->map(function ($i) use ($debut) {
            $d = $debut->copy()->addMonths($i);
            return [
                'mois' => $d->translatedFormat('M Y'),
                'n' => Stage::whereBetween('created_at', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])->count(),
            ];
        });

        // Pipeline candidatures
        $pipelineCandidatures = Candidature::select('status', DB::raw('count(*) as n'))
            ->groupBy('status')->pluck('n', 'status');
        $pipelineCandidatures = collect(['pending', 'accepted', 'rejected'])
            ->mapWithKeys(fn ($s) => [$s => (int) ($pipelineCandidatures[$s] ?? 0)]);

        // Distribution des notes jury (binning par tranche de 2 points)
        $notes = Stage::whereNotNull('jury_note')->pluck('jury_note');
        $tranches = ['0-8', '8-10', '10-12', '12-14', '14-16', '16-18', '18-20'];
        $bornes = [[0,8],[8,10],[10,12],[12,14],[14,16],[16,18],[18,20.01]];
        $distributionNotes = collect($tranches)->mapWithKeys(function ($t, $i) use ($notes, $bornes) {
            [$min, $max] = $bornes[$i];
            return [$t => $notes->filter(fn ($n) => $n >= $min && $n < $max)->count()];
        });
        $moyenneJury = $notes->count() ? round($notes->avg(), 2) : null;

        // Top 5 lieux d'offres publiées
        $topLieux = Offre::published()
            ->select('lieu', DB::raw('count(*) as n'))
            ->whereNotNull('lieu')->where('lieu', '!=', '')
            ->groupBy('lieu')->orderByDesc('n')->limit(5)->get();

        // Étapes signature convention (% de signatures par étape)
        $etapesConvention = [
            'Étudiant' => Convention::whereNotNull('signed_student_at')->count(),
            'Entreprise' => Convention::whereNotNull('signed_company_at')->count(),
            'Tuteur' => Convention::whereNotNull('signed_tutor_at')->count(),
            'Admin' => Convention::whereNotNull('validated_admin_at')->count(),
        ];

        // Délai moyen signature complète (en jours, sur conventions complètes)
        $delaiMoyen = Convention::whereNotNull('signed_student_at')
            ->whereNotNull('validated_admin_at')
            ->get()
            ->map(fn ($c) => $c->signed_student_at->diffInDays($c->validated_admin_at))
            ->avg();
        $delaiMoyen = $delaiMoyen !== null ? round($delaiMoyen, 1) : null;

        // Durée moyenne stages (semaines) par formation — calcul PHP (cross-DB)
        $dureesParFormation = Stage::with('student.profilEtudiant.formation')
            ->whereNotNull('date_debut')->whereNotNull('date_fin')
            ->get()
            ->groupBy(fn ($s) => optional(optional($s->student?->profilEtudiant)->formation)->intitule)
            ->filter(fn ($v, $k) => $k)
            ->map(fn ($g) => round($g->avg(fn ($s) => $s->date_debut->diffInDays($s->date_fin) / 7), 1))
            ->sortDesc()
            ->take(5);

        return [
            'nombreUtilisateurs' => Utilisateur::count(),
            'utilisateursEnAttente' => Utilisateur::where('is_active', false)->count(),
            'demandesEnAttente' => DemandeFormation::where('status', 'pending')->count(),
            'nombreOffres' => Offre::count(),
            'nombreEtudiants' => Utilisateur::where('role', Utilisateur::ROLE_ETUDIANT)->count(),
            'nombreEntreprises' => Utilisateur::where('role', Utilisateur::ROLE_ENTREPRISE)->count(),
            'entreprisesAValider' => ProfilEntreprise::where('is_validated', false)->count()
                + Utilisateur::where('role', Utilisateur::ROLE_ENTREPRISE)->whereDoesntHave('profilEntreprise')->count(),
            'candidaturesTotal' => Candidature::count(),
            'candidaturesEnAttente' => Candidature::where('status', Candidature::STATUS_PENDING)->count(),
            'stagesParStatut' => $stagesParStatut,
            'stagesTotal' => $stagesParStatut->sum(),
            'stagesValides' => $stagesParStatut['valide'] ?? 0,
            'signeesCompletes' => $signeesCompletes,
            'conventionsTotal' => $conventionsTotal,
            'tauxSignature' => $conventionsTotal ? round($signeesCompletes / $conventionsTotal * 100) : 0,
            'offresPubliees' => (int) ($offresParStatut['published'] ?? 0),
            'topFormations' => $topFormations,
            'serieMensuelle' => $serieMensuelle,
            'pipelineCandidatures' => $pipelineCandidatures,
            'distributionNotes' => $distributionNotes,
            'moyenneJury' => $moyenneJury,
            'topLieux' => $topLieux,
            'etapesConvention' => $etapesConvention,
            'delaiMoyen' => $delaiMoyen,
            'dureesParFormation' => $dureesParFormation,
        ];
    }
}
