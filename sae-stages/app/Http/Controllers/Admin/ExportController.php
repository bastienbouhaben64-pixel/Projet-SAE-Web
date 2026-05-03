<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Stage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function stages(Request $request): StreamedResponse
    {
        $f = $request->only(['q', 'status', 'formation_id', 'tuteur', 'convention', 'debut', 'fin', 'vue']);

        $q = Stage::with(['offer', 'student.profilEtudiant.formation', 'company.profilEntreprise', 'tutor', 'jury', 'convention'])
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['formation_id'] ?? null, fn ($q, $v) => $q->whereHas('student.profilEtudiant', fn ($p) => $p->where('formation_id', $v)))
            ->when($f['tuteur'] ?? null, function ($q, $v) {
                if ($v === 'sans') return $q->whereNull('tutor_id');
                if ($v === 'avec') return $q->whereNotNull('tutor_id');
                return $q->where('tutor_id', $v);
            })
            ->when($f['debut'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($f['fin'] ?? null, fn ($q, $v) => $q->whereDate('date_fin', '<=', $v))
            ->when($f['q'] ?? null, function ($q, $v) {
                $q->where(function ($w) use ($v) {
                    $w->whereHas('offer', fn ($o) => $o->where('titre', 'like', "%{$v}%"))
                      ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$v}%"))
                      ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$v}%"));
                });
            });
        if (($f['vue'] ?? null) === 'archives') $q->archives(); else $q->actifs();
        $stages = $q->latest()->get();

        ActivityLogger::log('admin.export.stages', ['count' => $stages->count()]);

        return $this->stream('stages-'.now()->format('Ymd-His').'.csv', [
            'ID','Statut','Archivé','Étudiant','Email étudiant','Formation','Entreprise','Email entreprise',
            'Tuteur','Offre','Lieu','Durée (sem.)','Date début','Date fin',
            'Convention complète','Note jury /20','Date validation','Jury',
        ], $stages, function (Stage $s) {
            $conv = $s->convention;
            $signed = $conv && $conv->signed_student_at && $conv->signed_company_at && $conv->signed_tutor_at && $conv->validated_admin_at;
            return [
                $s->id,
                $s->status,
                $s->archived_at?->format('Y-m-d') ?: '',
                $s->student->name,
                $s->student->email,
                optional($s->student->profilEtudiant?->formation)->intitule ?? '',
                optional($s->company->profilEntreprise)->raison_sociale ?? $s->company->name,
                $s->company->email,
                $s->tutor?->name ?? '',
                $s->offer->titre,
                $s->offer->lieu,
                $s->offer->duree_semaines,
                $s->date_debut?->format('Y-m-d') ?? '',
                $s->date_fin?->format('Y-m-d') ?? '',
                $signed ? 'oui' : 'non',
                $s->jury_note !== null ? number_format((float) $s->jury_note, 2, '.', '') : '',
                $s->validated_at?->format('Y-m-d H:i') ?? '',
                $s->jury?->name ?? '',
            ];
        });
    }

    public function candidatures(Request $request): StreamedResponse
    {
        $candidatures = Candidature::with(['offer.company.profilEntreprise', 'student.profilEtudiant.formation'])
            ->latest()->get();

        ActivityLogger::log('admin.export.candidatures', ['count' => $candidatures->count()]);

        return $this->stream('candidatures-'.now()->format('Ymd-His').'.csv', [
            'ID','Date','Statut','Étudiant','Email','Formation','Offre','Entreprise','Décidée le','Commentaire décision',
        ], $candidatures, function (Candidature $c) {
            return [
                $c->id,
                $c->created_at?->format('Y-m-d H:i') ?? '',
                $c->status,
                $c->student->name,
                $c->student->email,
                optional($c->student->profilEtudiant?->formation)->intitule ?? '',
                $c->offer->titre,
                optional($c->offer->company->profilEntreprise)->raison_sociale ?? $c->offer->company->name,
                $c->decided_at?->format('Y-m-d H:i') ?? '',
                $c->decision_comment ?? '',
            ];
        });
    }

    private function stream(string $filename, array $header, $rows, callable $mapper): StreamedResponse
    {
        return new StreamedResponse(function () use ($header, $rows, $mapper) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            foreach ($rows as $r) {
                fputcsv($out, $mapper($r), ';');
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
