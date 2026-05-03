@extends('layouts.app')
@section('title', $offre->exists ? 'Modifier l\'offre' : 'Nouvelle offre')
@section('content')
<h2 class="mb-3">{{ $offre->exists ? 'Modifier l\'offre' : 'Nouvelle offre' }}</h2>
<form method="POST" action="{{ $offre->exists ? route('offres.maj', $offre) : route('offres.enregistrer') }}" class="card p-3">
    @csrf
    @if ($offre->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Titre</label>
            <input class="form-control" name="titre" value="{{ old('titre', $offre->titre) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Statut</label>
            <select name="status" class="form-select">
                @foreach (['draft' => 'Brouillon', 'published' => 'Publiée', 'archived' => 'Archivée'] as $k => $v)
                    <option value="{{ $k }}" @selected(old('status', $offre->status ?? 'draft') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Lieu</label>
            <input class="form-control" name="lieu" value="{{ old('lieu', $offre->lieu) }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Durée (semaines)</label>
            <input type="number" min="1" max="104" class="form-control" name="duree_semaines" value="{{ old('duree_semaines', $offre->duree_semaines) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date de début</label>
            <input type="date" class="form-control" name="date_debut" value="{{ old('date_debut', optional($offre->date_debut)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Rémunération</label>
            <input class="form-control" name="remuneration" value="{{ old('remuneration', $offre->remuneration) }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Domaine</label>
            <input class="form-control" name="domaine" value="{{ old('domaine', $offre->domaine) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Formation cible</label>
            <select name="formation_id" class="form-select">
                <option value="">— Aucune —</option>
                @foreach ($formations as $f)
                    <option value="{{ $f->id }}" @selected(old('formation_id', $offre->formation_id) == $f->id)>{{ $f->intitule }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="8" required>{{ old('description', $offre->description) }}</textarea>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('offres.miennes') }}" class="btn btn-link">Annuler</a>
    </div>
</form>
@endsection
