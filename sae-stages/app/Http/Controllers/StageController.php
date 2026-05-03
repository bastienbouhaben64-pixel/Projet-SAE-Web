<?php

namespace App\Http\Controllers;

use App\Models\Convention;
use App\Models\Stage;
use App\Models\RemarqueStage;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /** Liste accessible selon le rôle. */
    public function index(Request $request)
    {
        $utilisateur = $request->user();
        $q = Stage::with(['offer', 'student', 'company.companyProfile', 'tutor', 'convention']);

        if ($utilisateur->isEtudiant()) {
            $q->where('student_id', $utilisateur->id);
        } elseif ($utilisateur->isEntreprise()) {
            $q->where('company_id', $utilisateur->id);
        } elseif ($utilisateur->isProfesseur()) {
            $q->where('tutor_id', $utilisateur->id);
        } elseif ($utilisateur->isJury() || $utilisateur->isAdmin()) {
            // tout
        } else {
            abort(403);
        }

        $stages = $q->latest()->paginate(20);
        return view('stages.index', compact('stages'));
    }

    public function show(Stage $stage, Request $request)
    {
        $this->authorizeView($stage, $request);
        $stage->load(['offer', 'student.studentProfile.formation', 'company.companyProfile',
            'tutor', 'jury', 'convention', 'documents.uploader',
            'cahierEntries', 'remarks.author', 'missions']);
        return view('stages.afficher', compact('stage'));
    }

    public function addRemark(Stage $stage, Request $request)
    {
        $this->authorizeView($stage, $request);
        $data = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
            'scope' => ['required', 'in:general,rapport'],
        ]);
        RemarqueStage::create([
            'stage_id' => $stage->id,
            'author_id' => $request->user()->id,
            'author_role' => $request->user()->role,
            'contenu' => $data['contenu'],
            'scope' => $data['scope'],
        ]);
        ActivityLogger::log('stage.remark.added', ['stage_id' => $stage->id]);
        return back()->with('status', 'Remarque ajoutée.');
    }

    /** Jury valide le stage. */
    public function validateByJury(Stage $stage, Request $request)
    {
        abort_unless($request->user()->isJury() || $request->user()->isAdmin(), 403);

        $rules = [
            'jury_comment' => ['nullable', 'string', 'max:2000'],
            'jury_note' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'criteres' => ['nullable', 'array'],
        ];
        foreach (array_keys(Stage::CRITERES_JURY) as $k) {
            $rules['criteres.'.$k] = ['nullable', 'integer', 'min:0', 'max:5'];
        }
        $data = $request->validate($rules);

        $grille = collect($data['criteres'] ?? [])
            ->only(array_keys(Stage::CRITERES_JURY))
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->all();

        // Si aucune note globale fournie, calcule la moyenne des critères /5 → /20
        $note = $data['jury_note'] ?? null;
        if ($note === null && ! empty($grille)) {
            $note = round((array_sum($grille) / count($grille)) * 4, 2);
        }

        $stage->update([
            'status' => Stage::STATUS_VALIDE,
            'jury_id' => $request->user()->id,
            'jury_comment' => $data['jury_comment'] ?? null,
            'jury_note' => $note,
            'jury_grille' => $grille ?: null,
            'validated_at' => now(),
        ]);
        ActivityLogger::log('stage.jury_validated', ['stage_id' => $stage->id]);
        Notify::send($stage->student_id, 'stage.validated', 'Stage validé par le jury', $data['jury_comment'] ?? null, route('stages.afficher', $stage));
        if ($stage->tutor_id) {
            Notify::send($stage->tutor_id, 'stage.validated', 'Stage validé par le jury', null, route('stages.afficher', $stage));
        }
        return back()->with('status', 'Stage validé.');
    }

    /** Étudiant marque le stage comme terminé (pour passer à validation jury). */
    public function markAsEnded(Stage $stage, Request $request)
    {
        abort_unless($stage->student_id === $request->user()->id || $request->user()->isAdmin(), 403);
        abort_unless($stage->status === Stage::STATUS_EN_COURS, 422, 'Le stage doit être en cours.');
        $stage->update(['status' => Stage::STATUS_TERMINE]);
        ActivityLogger::log('stage.ended', ['stage_id' => $stage->id]);
        Notify::broadcastToRole('jury', 'stage.ready_for_jury', "Stage à évaluer : {$stage->offer->titre}", null, route('stages.afficher', $stage));
        return back()->with('status', 'Stage marqué comme terminé.');
    }

    /** Export PDF du dossier complet de stage. */
    public function exportPdf(Stage $stage, Request $request)
    {
        $this->authorizeView($stage, $request);
        $stage->load(['offer.formation', 'student.studentProfile.formation', 'company.companyProfile',
            'tutor', 'jury', 'convention', 'documents.uploader',
            'cahierEntries', 'remarks.author', 'missions']);
        $pdf = Pdf::loadView('stages.dossier_pdf', compact('stage'))->setPaper('A4');
        ActivityLogger::log('stage.pdf_exported', ['stage_id' => $stage->id]);
        $filename = 'dossier-stage-'.$stage->id.'-'.\Illuminate\Support\Str::slug($stage->student->name).'.pdf';
        return $pdf->download($filename);
    }

    /** Vérifie l'accès d'un user au détail d'un stage. */
    private function authorizeView(Stage $stage, Request $request): void
    {
        $u = $request->user();
        $ok = $u->isAdmin() || $u->isJury()
            || $stage->student_id === $u->id
            || $stage->company_id === $u->id
            || $stage->tutor_id === $u->id;
        abort_unless($ok, 403);
    }
}
