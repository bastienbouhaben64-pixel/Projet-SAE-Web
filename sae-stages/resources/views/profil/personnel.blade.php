@extends('layouts.app')
@section('title', 'Mon profil')
@section('content')
<h2 class="mb-3">
    <i class="bi bi-{{ $profilUser->isJury() ? 'award' : 'mortarboard' }}"></i>
    Mon profil {{ $profilUser->isJury() ? 'jury' : 'professeur' }}
</h2>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@include('profil._compte')

<div class="card p-3 mb-3">
    <h5 class="mb-3"><i class="bi bi-person-vcard"></i> Informations professionnelles</h5>
    <form method="POST" action="{{ route('profil.personnel') }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input class="form-control" name="telephone" value="{{ old('telephone', $profilUser->telephone) }}" placeholder="06 12 34 56 78">
            </div>
            <div class="col-md-6">
                <label class="form-label">Spécialités</label>
                <input class="form-control" name="specialites" value="{{ old('specialites', $profilUser->specialites) }}" placeholder="Web, Cybersécurité, Data, IA…">
                <div class="form-text">Mots-clés séparés par des virgules</div>
            </div>
            <div class="col-12">
                <label class="form-label">Présentation</label>
                <textarea class="form-control" name="bio" rows="4" placeholder="Quelques lignes pour vous présenter aux étudiants et entreprises…">{{ old('bio', $profilUser->bio) }}</textarea>
            </div>
            <div class="col-12 form-check ms-2">
                <input type="hidden" name="disponible" value="0">
                <input type="checkbox" class="form-check-input" id="dispo" name="disponible" value="1" @checked(old('disponible', $profilUser->disponible))>
                <label class="form-check-label" for="dispo">
                    Disponible pour {{ $profilUser->isJury() ? 'évaluer de nouveaux stages' : 'encadrer de nouveaux stages' }}
                </label>
            </div>
        </div>
        <div class="text-end mt-3">
            <button class="btn btn-brand">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
