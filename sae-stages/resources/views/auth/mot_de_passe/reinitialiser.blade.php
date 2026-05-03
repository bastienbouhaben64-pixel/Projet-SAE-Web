@extends('layouts.auth')
@section('title', 'Réinitialiser le mot de passe')
@section('content')
    <h1 class="text-center"><i class="bi bi-shield-lock"></i> Nouveau mot de passe</h1>

    <form method="POST" action="{{ route('mot_de_passe.reinitialiser') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label">Adresse email</label>
            <input type="email" required name="email" class="form-control" value="{{ old('email', $email) }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" required name="password" class="form-control" minlength="8" autofocus>
            <div class="form-text">8 caractères minimum.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmation</label>
            <input type="password" required name="password_confirmation" class="form-control" minlength="8">
        </div>
        <button class="btn btn-brand w-100">Mettre à jour</button>
    </form>
@endsection
