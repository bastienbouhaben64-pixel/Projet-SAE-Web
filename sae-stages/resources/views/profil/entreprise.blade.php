@extends('layouts.app')
@section('title', 'Mon profil')
@section('content')
<h2 class="mb-3"><i class="bi bi-building"></i> Mon profil entreprise</h2>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@include('profil._compte')

<div class="card p-3 mb-3">
    <h5 class="mb-3"><i class="bi bi-shop"></i> Informations société</h5>
    @if (! $profil->is_validated)
        <div class="alert alert-warning small mb-3">
            <i class="bi bi-hourglass-split"></i> Votre fiche entreprise est en attente de validation par un administrateur.
            Vos offres ne seront pas publiées tant que la validation n'est pas effectuée.
        </div>
    @else
        <div class="alert alert-success small mb-3">
            <i class="bi bi-patch-check"></i> Fiche validée par l'administration.
        </div>
    @endif

    <form method="POST" action="{{ route('profil.entreprise') }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Raison sociale *</label>
                <input class="form-control @error('raison_sociale') is-invalid @enderror" name="raison_sociale" value="{{ old('raison_sociale', $profil->raison_sociale) }}" required>
                @error('raison_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">SIRET</label>
                <input class="form-control @error('siret') is-invalid @enderror" name="siret" value="{{ old('siret', $profil->siret) }}" placeholder="14 chiffres">
                @error('siret')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Secteur</label>
                <input class="form-control" name="secteur" value="{{ old('secteur', $profil->secteur) }}" placeholder="Informatique, Industrie…">
            </div>
            <div class="col-md-6">
                <label class="form-label">Site web</label>
                <input type="url" class="form-control @error('site_web') is-invalid @enderror" name="site_web" value="{{ old('site_web', $profil->site_web) }}" placeholder="https://exemple.com">
                @error('site_web')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Adresse</label>
                <input class="form-control" name="adresse" value="{{ old('adresse', $profil->adresse) }}" placeholder="12 rue …, 75000 Paris">
            </div>
        </div>
        <div class="text-end mt-3">
            <button class="btn btn-brand">Enregistrer le profil</button>
        </div>
    </form>
</div>
@endsection
