@extends('layouts.app')
@section('title', 'Offres de stage')
@section('content')
<h2 class="mb-3"><i class="bi bi-briefcase"></i> Offres de stage</h2>

<form method="GET" class="card p-3 mb-3">
    <datalist id="lieux-connus">@foreach ($lieuxConnus as $l)<option value="{{ $l }}">@endforeach</datalist>
    <datalist id="domaines-connus">@foreach ($domainesConnus as $d)<option value="{{ $d }}">@endforeach</datalist>
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label small mb-1">Recherche</label>
            <input class="form-control form-control-sm" name="q" placeholder="Titre, description…" value="{{ $filters['q'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Lieu</label>
            <input class="form-control form-control-sm" list="lieux-connus" name="lieu" placeholder="Paris, Lyon…" value="{{ $filters['lieu'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Domaine</label>
            <input class="form-control form-control-sm" list="domaines-connus" name="domaine" placeholder="Web, Data…" value="{{ $filters['domaine'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Formation ciblée</label>
            <select name="formation_id" class="form-select form-select-sm">
                <option value="">— toutes —</option>
                @foreach ($formations as $f)
                    <option value="{{ $f->id }}" @selected(($filters['formation_id'] ?? null) == $f->id)>{{ $f->intitule }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small mb-1">Durée min (sem.)</label>
            <input class="form-control form-control-sm" type="number" min="1" name="duree_min" value="{{ $filters['duree_min'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Durée max (sem.)</label>
            <input class="form-control form-control-sm" type="number" min="1" name="duree_max" value="{{ $filters['duree_max'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Début ≥</label>
            <input class="form-control form-control-sm" type="date" name="debut_apres" value="{{ $filters['debut_apres'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Trier par</label>
            <select name="tri" class="form-select form-select-sm">
                @foreach ([
                    'recent' => 'Plus récentes',
                    'ancien' => 'Plus anciennes',
                    'debut' => 'Début le plus proche',
                    'duree_asc' => 'Durée croissante',
                    'duree_desc' => 'Durée décroissante',
                ] as $k => $libelle)
                    <option value="{{ $k }}" @selected(($filters['tri'] ?? 'recent') === $k)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
                <input type="hidden" name="remunere" value="">
                <input class="form-check-input" type="checkbox" id="remunere" name="remunere" value="1" @checked(($filters['remunere'] ?? null) === '1')>
                <label class="form-check-label small" for="remunere">Rémunérées uniquement</label>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted">{{ $offres->total() }} offre(s) trouvée(s)</span>
        <div>
            <a href="{{ route('offres.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            <button class="btn btn-sm btn-brand"><i class="bi bi-funnel"></i> Filtrer</button>
        </div>
    </div>
</form>

<div class="row g-3">
    @forelse ($offres as $o)
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1"><a href="{{ route('offres.afficher', $o) }}" class="text-decoration-none">{{ $o->titre }}</a></h5>
                    @if ($o->domaine)<span class="badge bg-info">{{ $o->domaine }}</span>@endif
                </div>
                <div class="text-muted small mb-2">
                    <i class="bi bi-building"></i> {{ optional($o->company->companyProfile)->raison_sociale ?? $o->company->name }}
                    · <i class="bi bi-geo-alt"></i> {{ $o->lieu }}
                    · <i class="bi bi-calendar"></i> {{ $o->duree_semaines }} sem.
                    @if ($o->formation) · {{ $o->formation->intitule }} @endif
                    @if ($o->remuneration) · <i class="bi bi-cash"></i> {{ $o->remuneration }} @endif
                </div>
                <p class="mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($o->description), 180) }}</p>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info">Aucune offre ne correspond.</div></div>
    @endforelse
</div>

<div class="mt-3">{{ $offres->links() }}</div>
@endsection
