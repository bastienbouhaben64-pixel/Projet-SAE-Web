@extends('layouts.app')
@section('title', 'Tableau de bord — Entreprise')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Mon espace entreprise</h2>
    <a href="{{ route('offres.creer') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nouvelle offre</a>
</div>
<div class="card p-3">
    <h5>Mes dernières offres ({{ $totalOffres }})</h5>
    @forelse ($mesOffres as $o)
        <div class="py-2 border-bottom d-flex justify-content-between">
            <div>
                <strong>{{ $o->titre }}</strong> · {{ $o->lieu }}
                <span class="badge bg-{{ $o->status === 'published' ? 'success' : ($o->status === 'archived' ? 'secondary' : 'warning') }}">{{ $o->status }}</span>
            </div>
            <a href="{{ route('offres.modifier', $o) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
        </div>
    @empty
        <p class="text-muted">Vous n'avez pas encore d'offre.</p>
    @endforelse
</div>
@endsection
