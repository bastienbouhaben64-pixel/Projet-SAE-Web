@extends('layouts.auth')
@section('title', 'Mot de passe oublié')
@section('content')
    <h1 class="text-center"><i class="bi bi-key"></i> Mot de passe oublié</h1>
    <p class="text-center text-muted small">Saisissez votre email, nous vous enverrons un lien de réinitialisation.</p>

    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="POST" action="{{ route('mot_de_passe.envoyer_lien') }}">@csrf
        <div class="mb-3">
            <label class="form-label">Adresse email</label>
            <input type="email" required name="email" class="form-control" value="{{ old('email') }}" autofocus>
        </div>
        <button class="btn btn-brand w-100">Envoyer le lien</button>
    </form>

    <p class="text-center small mt-3">
        <a href="{{ route('connexion') }}">← Retour à la connexion</a>
    </p>
@endsection
