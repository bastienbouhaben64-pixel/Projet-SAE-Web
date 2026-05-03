@extends('layouts.auth')
@section('title', 'Vérification 2FA')
@section('content')
    <h1 class="text-center"><i class="bi bi-shield-lock"></i> Code de vérification</h1>
    <p class="subtitle text-center">Un code aléatoire à 6 chiffres vient d'être envoyé à votre adresse e-mail.</p>

    <form method="POST" action="{{ route('otp.verifier') }}">
        @csrf
        <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
               class="form-control otp-input mb-3" placeholder="······" required autofocus>
        <button class="btn btn-brand w-100">Valider</button>
    </form>
    <form method="POST" action="{{ route('otp.renvoyer') }}" class="mt-2">@csrf
        <button class="btn btn-link w-100">Renvoyer un code</button>
    </form>
@endsection
@push('scripts')
<script>
document.querySelector('input[name="code"]')?.addEventListener('input', function () {
    this.value = this.value.replace(/\D+/g, '').slice(0, 6);
});
</script>
@endpush
