@extends('layouts.app')
@section('title', 'Paramètres administrateur')
@section('content')
<h2 class="mb-4"><i class="bi bi-gear"></i> Paramètres administrateur</h2>

<div class="card p-4">
    <form method="POST" action="{{ route('admin.parametres.maj') }}">
        @csrf
        @method('PUT')

        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h5 class="mb-1">Authentification à deux facteurs par email</h5>
                <p class="text-muted mb-0">
                    Quand cette option est activée, chaque connexion valide le mot de passe puis envoie un code OTP à 6 chiffres par email.
                    Quand elle est désactivée, les utilisateurs accèdent directement au tableau de bord après email, mot de passe et rôle valides.
                </p>
            </div>
            <div class="form-check form-switch fs-5">
                <input type="hidden" name="email_2fa_enabled" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="email_2fa_enabled" name="email_2fa_enabled" value="1" @checked($email2faEnabled)>
                <label class="form-check-label" for="email_2fa_enabled">
                    {{ $email2faEnabled ? 'Activée' : 'Désactivée' }}
                </label>
            </div>
        </div>

        <hr>

        <div class="alert alert-info mb-3">
            Par défaut, l'A2F email est désactivée pour faciliter les démonstrations en localhost et permettre aux nouveaux comptes de se connecter sans dépendre du SMTP.
        </div>

        <button class="btn btn-brand"><i class="bi bi-save"></i> Enregistrer les paramètres</button>
    </form>
</div>
@endsection
