@extends('layouts.app')
@section('title', 'Admin · Stages')
@section('content')
<h2 class="mb-3"><i class="bi bi-briefcase"></i> Stages — affectation & archivage</h2>

@if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

<div class="d-flex justify-content-between align-items-center mb-3">
<div class="btn-group">
    <a href="{{ route('admin.stages.index', ['vue' => 'actifs']) }}" class="btn btn-sm {{ $vue==='actifs' ? 'btn-brand' : 'btn-outline-brand' }}">Actifs <span class="badge bg-light text-dark ms-1">{{ $compteurs['actifs'] }}</span></a>
    <a href="{{ route('admin.stages.index', ['vue' => 'archives']) }}" class="btn btn-sm {{ $vue==='archives' ? 'btn-brand' : 'btn-outline-brand' }}">Archivés <span class="badge bg-light text-dark ms-1">{{ $compteurs['archives'] }}</span></a>
</div>
<div>
    <a href="{{ route('admin.exports.stages') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export CSV</a>
</div>
</div>

<form method="GET" class="card p-3 mb-3">
    <input type="hidden" name="vue" value="{{ $vue }}">
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label small mb-1">Recherche</label>
            <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Étudiant, entreprise, offre…">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Statut</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">— tous —</option>
                @foreach (['brouillon','convention','en_cours','termine','valide'] as $s)
                    <option value="{{ $s }}" @selected(($filtres['status'] ?? null) === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Formation</label>
            <select name="formation_id" class="form-select form-select-sm">
                <option value="">— toutes —</option>
                @foreach ($formations as $f)
                    <option value="{{ $f->id }}" @selected(($filtres['formation_id'] ?? null) == $f->id)>{{ $f->intitule }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Tuteur</label>
            <select name="tuteur" class="form-select form-select-sm">
                <option value="">— tous —</option>
                <option value="sans" @selected(($filtres['tuteur'] ?? null) === 'sans')>Sans tuteur</option>
                <option value="avec" @selected(($filtres['tuteur'] ?? null) === 'avec')>Avec tuteur</option>
                <optgroup label="Professeurs">
                    @foreach ($tuteurs as $t)
                        <option value="{{ $t->id }}" @selected(($filtres['tuteur'] ?? null) == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Convention</label>
            <select name="convention" class="form-select form-select-sm">
                <option value="">— indifférent —</option>
                <option value="complete" @selected(($filtres['convention'] ?? null) === 'complete')>Complètement signée</option>
                <option value="partielle" @selected(($filtres['convention'] ?? null) === 'partielle')>Signature partielle</option>
                <option value="absente" @selected(($filtres['convention'] ?? null) === 'absente')>Aucune convention</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Début ≥</label>
            <input type="date" name="debut" value="{{ $filtres['debut'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Fin ≤</label>
            <input type="date" name="fin" value="{{ $filtres['fin'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-8 d-flex align-items-end justify-content-end gap-2">
            <a href="{{ route('admin.stages.index', ['vue' => $vue]) }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            <button class="btn btn-sm btn-brand"><i class="bi bi-funnel"></i> Filtrer</button>
        </div>
    </div>
</form>

<div class="card">
<table class="table mb-0">
    <thead><tr><th>Offre</th><th>Étudiant</th><th>Entreprise</th><th>Tuteur affecté</th><th>Statut</th><th>Période</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    @forelse ($stages as $s)
        <tr>
            <td><a href="{{ route('stages.afficher', $s) }}">{{ $s->offer->titre }}</a></td>
            <td>{{ $s->student->name }}</td>
            <td>{{ optional($s->company->companyProfile)->raison_sociale ?? $s->company->name }}</td>
            <td>{{ $s->tutor?->name ?? '—' }}</td>
            <td>
                <span class="badge bg-{{ ['brouillon'=>'secondary','convention'=>'warning','en_cours'=>'info','termine'=>'primary','valide'=>'success'][$s->status] }}">{{ $s->status }}</span>
                @if ($s->archived_at)
                    <span class="badge bg-dark ms-1" title="Archivé le {{ $s->archived_at->format('d/m/Y') }}"><i class="bi bi-archive"></i></span>
                @endif
            </td>
            <td class="small text-muted">{{ $s->date_debut?->format('d/m/Y') }} → {{ $s->date_fin?->format('d/m/Y') }}</td>
            <td class="text-end">
                @if (! $s->archived_at)
                    <form method="POST" action="{{ route('admin.stages.affecter_tuteur', $s) }}" class="d-inline-flex gap-1 align-items-center">@csrf
                        <select name="tutor_id" class="form-select form-select-sm tutor-select">
                            @foreach ($tuteurs as $t)
                                <option value="{{ $t->id }}" @selected($s->tutor_id===$t->id)>
                                    {{ $t->name }} {{ $t->disponible ? '· dispo' : '· indispo' }}@if($t->specialites) — {{ $t->specialites }}@endif
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-brand" title="Affecter"><i class="bi bi-person-plus"></i></button>
                    </form>
                    @if ($s->status === 'valide')
                        <form method="POST" action="{{ route('admin.stages.archiver', $s) }}" class="d-inline" onsubmit="return confirm('Archiver ce stage ?')">@csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Archiver"><i class="bi bi-archive"></i></button>
                        </form>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.stages.desarchiver', $s) }}" class="d-inline">@csrf
                        <button class="btn btn-sm btn-outline-secondary" title="Désarchiver"><i class="bi bi-arrow-counterclockwise"></i> Désarchiver</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Aucun stage.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $stages->links() }}</div>
@endsection
