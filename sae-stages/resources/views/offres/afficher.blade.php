@extends('layouts.app')
@section('title', $offre->titre)
@section('content')
<a href="{{ route('offres.index') }}" class="btn btn-link"><i class="bi bi-arrow-left"></i> Retour</a>
<div class="card p-4 mt-2">
    <h2>{{ $offre->titre }}</h2>
    <div class="text-muted mb-3">
        <i class="bi bi-building"></i> {{ optional($offre->company->companyProfile)->raison_sociale ?? $offre->company->name }}
        · <i class="bi bi-geo-alt"></i> {{ $offre->lieu }}
        · <i class="bi bi-calendar"></i> {{ $offre->duree_semaines }} semaines
        @if ($offre->date_debut) · début {{ $offre->date_debut->format('d/m/Y') }} @endif
        @if ($offre->remuneration) · {{ $offre->remuneration }} @endif
    </div>
    @if ($offre->domaine)<span class="badge bg-info">{{ $offre->domaine }}</span>@endif
    @if ($offre->formation)<span class="badge bg-secondary">{{ $offre->formation->intitule }}</span>@endif
    <hr>
    <div>{!! nl2br(e($offre->description)) !!}</div>

    @auth
        @if (auth()->user()->isEtudiant() && $offre->status === 'published')
            @php $existing = \App\Models\Candidature::where('offer_id', $offre->id)->where('student_id', auth()->id())->first(); @endphp
            <hr>
            @if ($existing)
                <div class="alert alert-info">
                    Vous avez déjà postulé — statut : <strong>{{ $existing->status }}</strong>
                    @if ($existing->status === 'pending')
                        <form method="POST" action="{{ route('candidatures.retirer', $existing) }}" class="d-inline ms-2">@csrf @method('PUT')
                            <button class="btn btn-sm btn-outline-secondary">Retirer</button>
                        </form>
                    @endif
                </div>
            @else
                <h5 class="mt-4">Postuler à cette offre</h5>
                <form method="POST" action="{{ route('candidatures.envoyer', $offre) }}">
                    @csrf
                    <textarea name="message" class="form-control mb-2" rows="3" placeholder="Message de motivation (facultatif)" maxlength="2000"></textarea>
                    <button class="btn btn-brand">Envoyer ma candidature</button>
                </form>
            @endif
        @endif
    @else
        <hr>
        <div class="alert alert-info mb-0">
            Vous pouvez consulter cette offre librement. Pour postuler, connectez-vous avec un compte étudiant.
            <a href="{{ route('connexion', ['role' => 'etudiant']) }}" class="alert-link">Se connecter</a>
            ou
            <a href="{{ route('inscription') }}" class="alert-link">créer un compte</a>.
        </div>
    @endauth
</div>
@endsection
