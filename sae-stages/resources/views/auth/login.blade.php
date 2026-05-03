@extends('layouts.auth')
@section('title', 'Connexion')
@section('content')
    <h1 class="text-center">Portail des Stages</h1>
    <p class="subtitle text-center">Identifiez-vous pour accéder à votre espace.</p>

    @php
        $roleLabels = [
            'etudiant' => 'Étudiant',
            'professeur' => 'Professeur (Tuteur)',
            'entreprise' => 'Entreprise',
            'jury' => 'Jury',
            'admin' => 'Administrateur',
        ];
        $current = old('role', $roleSelectionne);
    @endphp
    <form method="POST" action="{{ route('connexion') }}">
        @csrf
        <div class="mb-3">
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control" placeholder="Email institutionnel" required autofocus>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
        </div>
        <div class="mb-3">
            <div class="small text-muted mb-2">Vous êtes…</div>
            <div class="role-choice-grid">
                @foreach ($roleLabels as $key => $label)
                    <input class="btn-check" type="radio" name="role" id="role-{{ $key }}" value="{{ $key }}" required @checked($current === $key)>
                    <label class="btn btn-outline-primary role-choice" for="role-{{ $key }}">{{ $label }}</label>
                @endforeach
            </div>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Se souvenir de moi</label>
        </div>
        <button class="btn btn-brand w-100">Se connecter</button>
    </form>
@endsection
@section('links')
    <a href="{{ route('inscription') }}">Créer un compte</a> &nbsp;·&nbsp;
    <a href="{{ route('mot_de_passe.oublie') }}">Mot de passe oublié ?</a> &nbsp;|&nbsp;
@endsection
