@extends('layouts.app')
@section('title', 'Convention de stage')
@section('content')
@php
    $u = auth()->user();
    $isStudent = $u->id === $stage->student_id;
    $isCompany = $u->id === $stage->company_id;
    $isTutor = $u->id === $stage->tutor_id;
    $isAdmin = $u->isAdmin();
    $canEdit = $isAdmin || $isTutor || $isCompany;
@endphp
<a href="{{ route('stages.afficher', $stage) }}" class="btn btn-link"><i class="bi bi-arrow-left"></i> Retour au stage</a>
<h2 class="mb-1">Convention de stage</h2>
<div class="text-muted mb-3">{{ $stage->offer->titre }} · {{ $stage->student->name }} · {{ optional($stage->company->companyProfile)->raison_sociale ?? $stage->company->name }}</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card p-3">
            <form method="POST" action="{{ route('conventions.maj', $stage) }}">@csrf @method('PUT')
                <h5>Contenu de la convention</h5>
                <textarea name="contenu" rows="14" class="form-control" {{ $canEdit ? '' : 'readonly' }} placeholder="Contenu de la convention…">{{ old('contenu', $convention->contenu) }}</textarea>
                @if ($canEdit)
                    <button class="btn btn-sm btn-brand mt-3">Enregistrer</button>
                @endif
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-3">
            <h5>Signatures</h5>
            <ul class="list-unstyled small">
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span><i class="bi bi-person"></i> Étudiant</span>
                    @if ($convention->signed_student_at)
                        <span class="text-success">✓ {{ $convention->signed_student_at->format('d/m/Y H:i') }}</span>
                    @else <span class="text-muted">en attente</span> @endif
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span><i class="bi bi-building"></i> Entreprise</span>
                    @if ($convention->signed_company_at)
                        <span class="text-success">✓ {{ $convention->signed_company_at->format('d/m/Y H:i') }}</span>
                    @else <span class="text-muted">en attente</span> @endif
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span><i class="bi bi-mortarboard"></i> Tuteur</span>
                    @if ($convention->signed_tutor_at)
                        <span class="text-success">✓ {{ $convention->signed_tutor_at->format('d/m/Y H:i') }}</span>
                    @else <span class="text-muted">en attente</span> @endif
                </li>
                <li class="py-2 d-flex justify-content-between">
                    <span><i class="bi bi-shield-check"></i> Admin</span>
                    @if ($convention->validated_admin_at)
                        <span class="text-success">✓ {{ $convention->validated_admin_at->format('d/m/Y H:i') }}</span>
                    @else <span class="text-muted">en attente</span> @endif
                </li>
            </ul>

            @if (in_array($u->id, array_filter([$stage->student_id, $stage->company_id, $stage->tutor_id]), true))
                @php
                    $myField = match (true) {
                        $isStudent => 'signed_student_at',
                        $isCompany => 'signed_company_at',
                        $isTutor => 'signed_tutor_at',
                    };
                @endphp
                @if (! $convention->{$myField})
                    <form method="POST" action="{{ route('conventions.signer', $stage) }}" class="mt-2">@csrf
                        <button class="btn btn-brand w-100">Signer la convention</button>
                    </form>
                @else
                    <div class="alert alert-success mb-0 mt-2">Vous avez signé.</div>
                @endif
            @endif

            @if ($isAdmin && $convention->isFullySigned() && ! $convention->isAdminValidated())
                <form method="POST" action="{{ route('admin.conventions.valider', $stage) }}" class="mt-3">@csrf
                    <button class="btn btn-success w-100">Valider la convention (admin)</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
