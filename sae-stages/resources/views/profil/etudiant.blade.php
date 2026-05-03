@extends('layouts.app')
@section('title', 'Mon profil')
@section('content')
<h2 class="mb-3"><i class="bi bi-person-badge"></i> Mon profil étudiant</h2>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@include('profil._compte')

<div class="card p-3 mb-3">
    <h5 class="mb-3"><i class="bi bi-mortarboard"></i> Informations académiques</h5>
    <form method="POST" action="{{ route('profil.etudiant') }}" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Formation</label>
                <select class="form-select @error('formation_id') is-invalid @enderror" name="formation_id">
                    <option value="">— Choisir —</option>
                    @foreach ($formations as $f)
                        <option value="{{ $f->id }}" @selected(old('formation_id', $profil->formation_id) == $f->id)>{{ $f->intitule }}</option>
                    @endforeach
                </select>
                @error('formation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">
                    Votre formation n'est pas listée ?
                    <a href="{{ route('demandes_formation.creer') }}">Demandez son ajout</a>.
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Promotion</label>
                <input class="form-control" name="promo" value="{{ old('promo', $profil->promo) }}" placeholder="ex. 2025-2026">
            </div>
            <div class="col-md-3">
                <label class="form-label">Téléphone</label>
                <input class="form-control" name="telephone" value="{{ old('telephone', $profil->telephone) }}" placeholder="06 12 34 56 78">
            </div>

            <div class="col-md-12">
                <label class="form-label">CV (PDF, max 5 Mo)</label>
                <input type="file" class="form-control @error('cv') is-invalid @enderror" name="cv" accept="application/pdf">
                @error('cv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($profil->cv_path)
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <a href="{{ asset('storage/'.$profil->cv_path) }}" target="_blank" class="btn btn-sm btn-outline-brand">
                            <i class="bi bi-file-earmark-pdf"></i> Voir le CV actuel
                        </a>
                        <form method="POST" action="{{ route('profil.cv.supprimer') }}" onsubmit="return confirm('Supprimer le CV ?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        <div class="text-end mt-3">
            <button class="btn btn-brand">Enregistrer le profil</button>
        </div>
    </form>
</div>
@endsection
