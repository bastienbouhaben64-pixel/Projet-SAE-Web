@extends('layouts.app')
@section('title', 'Tableau de bord — Professeur')
@section('content')
<h2 class="mb-4">Espace professeur</h2>
<div class="card p-3">
    <h5>Dernières offres publiées</h5>
    @foreach ($offresRecentes as $o)
        <a href="{{ route('offres.afficher', $o) }}" class="d-block py-2 border-bottom text-decoration-none">
            <strong>{{ $o->titre }}</strong> · {{ $o->lieu }}
        </a>
    @endforeach
    <p class="text-muted small mt-3 mb-0">Le suivi des stages, l'affectation et la consultation des documents seront disponibles en phase 2.</p>
</div>
@endsection
