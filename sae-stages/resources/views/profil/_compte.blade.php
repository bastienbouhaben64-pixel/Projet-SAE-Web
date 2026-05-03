<div class="card p-3 mb-3">
    <h5 class="mb-3"><i class="bi bi-person-circle"></i> Mon compte</h5>
    <form method="POST" action="{{ route('profil.compte') }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom complet</label>
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Nouveau mot de passe <span class="text-muted small">(laisser vide pour conserver)</span></label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirmation</label>
                <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
            </div>
        </div>
        <div class="text-end mt-3">
            <button class="btn btn-brand">Enregistrer le compte</button>
        </div>
    </form>
</div>
