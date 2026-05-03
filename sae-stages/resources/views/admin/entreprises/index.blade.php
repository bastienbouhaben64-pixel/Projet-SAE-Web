@extends('layouts.app')
@section('title', 'Entreprises — validation')
@section('content')
<h2 class="mb-3"><i class="bi bi-building-check"></i> Entreprises</h2>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<form method="GET" class="row g-2 align-items-center mb-3">
    <div class="col-md-5">
        <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Recherche raison sociale, email, SIRET…">
    </div>
    <div class="col-md-4">
        <div class="btn-group" role="group">
            <a href="{{ route('admin.entreprises.index', ['filtre' => 'a_valider']) }}" class="btn btn-sm {{ $filtre==='a_valider' ? 'btn-brand' : 'btn-outline-brand' }}">
                À valider <span class="badge bg-light text-dark ms-1">{{ $compteurs['a_valider'] }}</span>
            </a>
            <a href="{{ route('admin.entreprises.index', ['filtre' => 'validees']) }}" class="btn btn-sm {{ $filtre==='validees' ? 'btn-brand' : 'btn-outline-brand' }}">
                Validées <span class="badge bg-light text-dark ms-1">{{ $compteurs['validees'] }}</span>
            </a>
            <a href="{{ route('admin.entreprises.index', ['filtre' => 'toutes']) }}" class="btn btn-sm {{ $filtre==='toutes' ? 'btn-brand' : 'btn-outline-brand' }}">
                Toutes <span class="badge bg-light text-dark ms-1">{{ $compteurs['toutes'] }}</span>
            </a>
        </div>
    </div>
    <div class="col-md-3 text-end">
        <button class="btn btn-sm btn-outline-secondary">Filtrer</button>
    </div>
</form>

<div class="card p-0">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th>Raison sociale</th>
            <th>Compte</th>
            <th>SIRET</th>
            <th>Secteur</th>
            <th>Inscrit le</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($entreprises as $u)
            @php $p = $u->profilEntreprise; @endphp
            <tr>
                <td>
                    <strong>{{ $p->raison_sociale ?? '— fiche non remplie —' }}</strong>
                    @if ($p?->site_web)
                        <a href="{{ $p->site_web }}" target="_blank" class="ms-1 small"><i class="bi bi-box-arrow-up-right"></i></a>
                    @endif
                    @if ($p?->adresse)
                        <div class="text-muted small">{{ $p->adresse }}</div>
                    @endif
                </td>
                <td>
                    {{ $u->name }}<br>
                    <span class="text-muted small">{{ $u->email }}</span>
                </td>
                <td class="small">{{ $p->siret ?? '—' }}</td>
                <td class="small">{{ $p->secteur ?? '—' }}</td>
                <td class="small">{{ $u->created_at->format('d/m/Y') }}</td>
                <td>
                    @if ($u->is_active && $p?->is_validated)
                        <span class="badge bg-success"><i class="bi bi-patch-check"></i> Validée</span>
                    @elseif (! $p)
                        <span class="badge bg-secondary">Fiche manquante</span>
                    @elseif (! $u->is_active)
                        <span class="badge bg-warning text-dark">Compte inactif</span>
                    @else
                        <span class="badge bg-info">Fiche à valider</span>
                    @endif
                </td>
                <td class="text-end">
                    @if (! ($u->is_active && $p?->is_validated))
                        <form method="POST" action="{{ route('admin.entreprises.valider', $u) }}" class="d-inline" @if (! $p) onsubmit="alert('Cette entreprise n\'a pas rempli sa fiche.'); return false;" @endif>@csrf
                            <button class="btn btn-sm btn-success" @disabled(! $p)><i class="bi bi-check2"></i> Valider</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.entreprises.rejeter', $u) }}" class="d-inline"
                          onsubmit="const m = prompt('Motif (optionnel) :'); if (m === null) return false; this.querySelector('[name=motif]').value = m;">
                        @csrf
                        <input type="hidden" name="motif">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Suspendre</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucune entreprise.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

<div class="mt-3">{{ $entreprises->links() }}</div>
@endsection
