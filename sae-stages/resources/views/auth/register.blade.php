@extends('layouts.auth')
@section('title', 'Inscription')
@section('content')
    <h1 class="text-center">Créer un compte</h1>
    <p class="subtitle text-center">Rejoignez la plateforme — votre compte sera activé par un administrateur.</p>

    <ul class="nav nav-pills justify-content-center mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $type === 'etudiant' ? 'active' : '' }}"
               href="{{ route('inscription', ['type' => 'etudiant']) }}">Étudiant</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'entreprise' ? 'active' : '' }}"
               href="{{ route('inscription', ['type' => 'entreprise']) }}">Entreprise</a>
        </li>
    </ul>

    <form method="POST" action="{{ route('inscription') }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="name" value="{{ old('name') }}" placeholder="{{ $type === 'entreprise' ? 'Nom du contact' : 'Nom complet' }}" required></div>
            <div class="col-md-6"><input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required></div>
            <div class="col-md-6"><input type="password" class="form-control" name="password" placeholder="Mot de passe" required minlength="8"></div>
            <div class="col-md-6"><input type="password" class="form-control" name="password_confirmation" placeholder="Confirmation" required minlength="8"></div>

            @if ($type === 'etudiant')
                <div class="col-md-6">
                    <select name="formation_id" class="form-select">
                        <option value="">— Formation (à demander) —</option>
                        @foreach ($formations as $f)
                            <option value="{{ $f->id }}">{{ $f->intitule }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input class="form-control" name="promo" placeholder="Promo" value="{{ old('promo') }}"></div>
                <div class="col-md-3"><input class="form-control" name="telephone" placeholder="Téléphone" value="{{ old('telephone') }}"></div>
            @else
                <div class="col-md-6"><input class="form-control" name="raison_sociale" placeholder="Raison sociale" value="{{ old('raison_sociale') }}" required></div>
                <div class="col-md-6"><input class="form-control" name="siret" placeholder="SIRET" value="{{ old('siret') }}"></div>
                <div class="col-md-6"><input class="form-control" name="secteur" placeholder="Secteur" value="{{ old('secteur') }}"></div>
                <div class="col-md-6"><input type="url" class="form-control" name="site_web" placeholder="Site web" value="{{ old('site_web') }}"></div>
                <div class="col-12"><input class="form-control" name="adresse" placeholder="Adresse" value="{{ old('adresse') }}"></div>
            @endif
        </div>
        <button class="btn btn-brand w-100 mt-3">Créer le compte</button>
    </form>
@endsection
@section('links')
    <a href="{{ route('connexion') }}">Déjà un compte ?</a> &nbsp;|&nbsp;
@endsection
