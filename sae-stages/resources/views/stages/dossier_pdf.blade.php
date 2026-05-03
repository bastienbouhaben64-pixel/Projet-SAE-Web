<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dossier de stage — {{ $stage->student->name }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
    h1 { font-size: 20px; color: #0c4a6e; margin: 0 0 4px 0; }
    h2 { font-size: 14px; color: #0c4a6e; border-bottom: 2px solid #0c4a6e; padding-bottom: 3px; margin: 18px 0 8px 0; }
    h3 { font-size: 12px; color: #1e3a8a; margin: 10px 0 4px 0; }
    .meta { color: #4b5563; font-size: 10px; margin-bottom: 14px; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; color: #fff; }
    .badge-valide { background: #16a34a; }
    .badge-en_cours { background: #0ea5e9; }
    .badge-termine { background: #6366f1; }
    .badge-convention { background: #f59e0b; }
    .badge-brouillon { background: #94a3b8; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table th, table td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
    table th { background: #f1f5f9; font-weight: bold; }
    .infogrid { width: 100%; }
    .infogrid td { border: none; padding: 2px 6px; }
    .infogrid td.label { color: #64748b; width: 30%; }
    .signed { color: #16a34a; font-weight: bold; }
    .pending { color: #94a3b8; font-style: italic; }
    .footer { position: fixed; bottom: 8px; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; }
    .pagebreak { page-break-after: always; }
    pre { white-space: pre-wrap; font-family: DejaVu Sans, sans-serif; background: #f8fafc; padding: 8px; border-left: 3px solid #0c4a6e; font-size: 10px; }
    .small { font-size: 9px; color: #64748b; }
</style>
</head>
<body>

<h1>Dossier de stage</h1>
<div class="meta">
    Document généré le {{ now()->format('d/m/Y \à H:i') }} — SAE Suivi de Stages, CY Tech
</div>

<table class="infogrid">
    <tr><td class="label">Étudiant</td><td><strong>{{ $stage->student->name }}</strong> — {{ $stage->student->email }}</td></tr>
    @if ($stage->student->studentProfile)
    <tr><td class="label">Formation</td><td>{{ optional($stage->student->studentProfile->formation)->intitule ?? '—' }}</td></tr>
    @endif
    <tr><td class="label">Entreprise</td><td>{{ optional($stage->company->companyProfile)->raison_sociale ?? $stage->company->name }} — {{ $stage->company->email }}</td></tr>
    <tr><td class="label">Tuteur académique</td><td>{{ $stage->tutor?->name ?? '—' }} {{ $stage->tutor ? '— '.$stage->tutor->email : '' }}</td></tr>
    <tr><td class="label">Offre</td><td>{{ $stage->offer->titre }} ({{ $stage->offer->lieu }} · {{ $stage->offer->duree_semaines }} sem.)</td></tr>
    <tr><td class="label">Période</td><td>{{ $stage->date_debut->format('d/m/Y') }} → {{ $stage->date_fin->format('d/m/Y') }}</td></tr>
    <tr><td class="label">Statut</td><td><span class="badge badge-{{ $stage->status }}">{{ str_replace('_',' ', $stage->status) }}</span></td></tr>
    @if ($stage->status === 'valide')
    <tr><td class="label">Validé par jury</td><td>{{ $stage->jury?->name }} — le {{ optional($stage->validated_at)->format('d/m/Y') }}</td></tr>
    @endif
</table>

<h2>1. Convention</h2>
@if ($stage->convention)
    <table class="infogrid">
        <tr><td class="label">Étudiant</td><td>@if ($stage->convention->signed_student_at)<span class="signed">✓ signée le {{ $stage->convention->signed_student_at->format('d/m/Y H:i') }}</span>@else <span class="pending">en attente</span>@endif</td></tr>
        <tr><td class="label">Entreprise</td><td>@if ($stage->convention->signed_company_at)<span class="signed">✓ signée le {{ $stage->convention->signed_company_at->format('d/m/Y H:i') }}</span>@else <span class="pending">en attente</span>@endif</td></tr>
        <tr><td class="label">Tuteur</td><td>@if ($stage->convention->signed_tutor_at)<span class="signed">✓ signée le {{ $stage->convention->signed_tutor_at->format('d/m/Y H:i') }}</span>@else <span class="pending">en attente</span>@endif</td></tr>
        <tr><td class="label">Validation admin</td><td>@if ($stage->convention->validated_admin_at)<span class="signed">✓ validée le {{ $stage->convention->validated_admin_at->format('d/m/Y H:i') }}</span>@else <span class="pending">en attente</span>@endif</td></tr>
    </table>
    @if ($stage->convention->contenu)
        <h3>Contenu</h3>
        <pre>{{ $stage->convention->contenu }}</pre>
    @endif
@else
    <p class="small">Aucune convention.</p>
@endif

<h2>2. Missions</h2>
@if ($stage->missions->isEmpty())
    <p class="small">Aucune mission assignée.</p>
@else
    <table>
        <thead><tr><th>Titre</th><th>Description</th><th>Échéance</th><th>Statut</th></tr></thead>
        <tbody>
        @foreach ($stage->missions as $m)
            <tr>
                <td><strong>{{ $m->titre }}</strong></td>
                <td>{{ $m->description ?: '—' }}</td>
                <td>{{ $m->due_date?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $m->status }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<h2>3. Cahier de stage</h2>
@if ($stage->cahierEntries->isEmpty())
    <p class="small">Aucune entrée.</p>
@else
    @foreach ($stage->cahierEntries as $e)
        <h3>{{ $e->date->format('d/m/Y') }} — {{ $e->titre }}</h3>
        <pre>{{ $e->contenu }}</pre>
    @endforeach
@endif

<div class="pagebreak"></div>

<h2>4. Documents déposés</h2>
@if ($stage->documents->isEmpty())
    <p class="small">Aucun document.</p>
@else
    <table>
        <thead><tr><th>Type</th><th>Titre</th><th>Déposé par</th><th>Date</th></tr></thead>
        <tbody>
        @foreach ($stage->documents as $d)
            <tr>
                <td>{{ $d->type }}</td>
                <td>{{ $d->titre }}</td>
                <td>{{ $d->uploader->name }}</td>
                <td>{{ $d->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<h2>5. Remarques</h2>
@if ($stage->remarks->isEmpty())
    <p class="small">Aucune remarque.</p>
@else
    @foreach ($stage->remarks as $r)
        <h3>{{ $r->author->name }} <span class="small">({{ $r->author_role }} · {{ $r->scope }} · {{ $r->created_at->format('d/m/Y H:i') }})</span></h3>
        <pre>{{ $r->contenu }}</pre>
    @endforeach
@endif

<h2>6. Évaluation jury</h2>
@if ($stage->status === 'valide')
    <table class="infogrid">
        <tr><td class="label">Jury</td><td>{{ $stage->jury->name }}</td></tr>
        <tr><td class="label">Date de validation</td><td>{{ $stage->validated_at->format('d/m/Y H:i') }}</td></tr>
        @if ($stage->jury_note !== null)
            <tr><td class="label">Note globale</td><td><strong>{{ rtrim(rtrim(number_format($stage->jury_note, 2, ',', ''), '0'), ',') }} / 20</strong></td></tr>
        @endif
    </table>
    @if ($stage->jury_grille)
        <h3>Grille d'évaluation</h3>
        <table>
            <thead><tr><th>Critère</th><th>Note</th></tr></thead>
            <tbody>
            @foreach (\App\Models\Stage::CRITERES_JURY as $k => $libelle)
                @if (isset($stage->jury_grille[$k]))
                    <tr><td>{{ $libelle }}</td><td>{{ $stage->jury_grille[$k] }} / 5</td></tr>
                @endif
            @endforeach
            </tbody>
        </table>
    @endif
    @if ($stage->jury_comment)
        <h3>Commentaire</h3>
        <pre>{{ $stage->jury_comment }}</pre>
    @endif
@else
    <p class="small">Stage non encore validé par le jury.</p>
@endif

<div class="footer">SAE Suivi de Stages · CY Tech · Document confidentiel</div>
</body>
</html>
