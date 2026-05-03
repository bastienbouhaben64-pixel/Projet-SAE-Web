@extends('layouts.app')
@section('title', 'Tableau de bord — Étudiant')
@section('content')
<h2 class="mb-4">Bienvenue {{ auth()->user()->name }}</h2>
<div class="row g-3">
    <div class="col-md-8">
        <div class="card p-3">
            <h5 class="mb-3">Dernières offres publiées</h5>
            @forelse ($offresRecentes as $o)
                <a href="{{ route('offres.afficher', $o) }}" class="d-block py-2 border-bottom text-decoration-none">
                    <strong>{{ $o->titre }}</strong>
                    <span class="text-muted"> · {{ $o->lieu }} · {{ $o->duree_semaines }} sem.</span>
                </a>
            @empty
                <p class="text-muted">Aucune offre pour le moment.</p>
            @endforelse
            <a href="{{ route('offres.index') }}" class="btn btn-primary mt-3">Voir toutes les offres</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Formation introuvable ?</h5>
            <p class="text-muted">Demandez à l'administrateur de l'ajouter.</p>
            <a href="{{ route('demandes_formation.creer') }}" class="btn btn-outline-primary">Faire une demande</a>
        </div>
    </div>
</div>
@endsection
