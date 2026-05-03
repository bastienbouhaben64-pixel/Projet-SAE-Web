<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Stage;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StageController extends Controller
{
    public function index(Request $request)
    {
        $vue = $request->query('vue', 'actifs');
        $filtres = $request->only(['q', 'status', 'formation_id', 'tuteur', 'convention', 'debut', 'fin']);

        $base = Stage::with(['offer', 'student.profilEtudiant.formation', 'company.profilEntreprise', 'tutor', 'convention'])
            ->when($filtres['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filtres['tuteur'] ?? null, function ($q, $v) {
                if ($v === 'sans') return $q->whereNull('tutor_id');
                if ($v === 'avec') return $q->whereNotNull('tutor_id');
                return $q->where('tutor_id', $v);
            })
            ->when($filtres['formation_id'] ?? null, fn ($q, $v) => $q->whereHas('student.profilEtudiant', fn ($p) => $p->where('formation_id', $v)))
            ->when($filtres['debut'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filtres['fin'] ?? null, fn ($q, $v) => $q->whereDate('date_fin', '<=', $v))
            ->when($filtres['convention'] ?? null, function ($q, $v) {
                return match ($v) {
                    'complete' => $q->whereHas('convention', fn ($c) => $c->whereNotNull('signed_student_at')->whereNotNull('signed_company_at')->whereNotNull('signed_tutor_at')->whereNotNull('validated_admin_at')),
                    'partielle' => $q->whereHas('convention', fn ($c) => $c->where(fn ($w) => $w->whereNull('signed_student_at')->orWhereNull('signed_company_at')->orWhereNull('signed_tutor_at')->orWhereNull('validated_admin_at'))),
                    'absente' => $q->whereDoesntHave('convention'),
                    default => $q,
                };
            })
            ->when($filtres['q'] ?? null, function ($q, $v) {
                $q->where(function ($w) use ($v) {
                    $w->whereHas('offer', fn ($o) => $o->where('titre', 'like', "%{$v}%"))
                      ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%"))
                      ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%"));
                });
            })
            ->latest();

        if ($vue === 'archives') $base->archives(); else $base->actifs();

        $stages = $base->paginate(20)->withQueryString();
        $tuteurs = Utilisateur::where('role', Utilisateur::ROLE_PROFESSEUR)->where('is_active', true)->orderBy('name')->get();
        // tuteurs disponibles en priorité dans le select
        $tuteurs = $tuteurs->sortByDesc(fn ($t) => (int) $t->disponible)->values();
        $formations = Formation::where('is_active', true)->orderBy('intitule')->get();
        $compteurs = [
            'actifs' => Stage::actifs()->count(),
            'archives' => Stage::archives()->count(),
        ];
        return view('admin.stages.index', compact('stages', 'tuteurs', 'formations', 'vue', 'compteurs', 'filtres'));
    }

    public function archive(Stage $stage)
    {
        abort_unless($stage->status === Stage::STATUS_VALIDE, 422, 'Seuls les stages validés par le jury peuvent être archivés.');
        $stage->update(['archived_at' => now()]);
        ActivityLogger::log('admin.stage.archived', ['stage_id' => $stage->id]);
        return back()->with('status', 'Stage archivé.');
    }

    public function unarchive(Stage $stage)
    {
        $stage->update(['archived_at' => null]);
        ActivityLogger::log('admin.stage.unarchived', ['stage_id' => $stage->id]);
        return back()->with('status', 'Stage désarchivé.');
    }

    public function assignTutor(Stage $stage, Request $request)
    {
        $data = $request->validate([
            'tutor_id' => ['required', Rule::exists('utilisateurs', 'id')],
        ]);
        $tuteur = Utilisateur::findOrFail($data['tutor_id']);
        abort_unless($tuteur->isProfesseur(), 422, "L'utilisateur doit être un professeur.");

        $stage->update([
            'tutor_id' => $tuteur->id,
            'status' => Stage::STATUS_CONVENTION,
        ]);

        // ensure convention exists
        $stage->convention()->firstOrCreate([], ['contenu' => null]);

        ActivityLogger::log('admin.stage.tutor_assigned', ['stage_id' => $stage->id, 'tutor_id' => $tuteur->id]);

        Notify::send($tuteur, 'stage.assigned', "Nouveau stage à encadrer : {$stage->offer->titre}",
            "Étudiant : {$stage->student->name}", route('stages.afficher', $stage));
        Notify::send($stage->student_id, 'stage.tutor_assigned', "Tuteur affecté : {$tuteur->name}",
            null, route('stages.afficher', $stage));
        Notify::send($stage->company_id, 'stage.tutor_assigned', "Tuteur affecté : {$tuteur->name}",
            null, route('stages.afficher', $stage));

        return back()->with('status', 'Tuteur affecté et convention initialisée.');
    }
}
