@extends('layouts.app')
@section('title', 'Candidatures reçues')
@section('content')
<h2 class="mb-3"><i class="bi bi-inbox"></i> Candidatures reçues</h2>
<form method="GET" class="mb-3">
    <select name="status" class="form-select form-select-sm w-auto d-inline-block">
        <option value="">Tous</option>
        @foreach (['pending','accepted','rejected','withdrawn'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-brand">Filtrer</button>
</form>

@forelse ($candidatures as $a)
    <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between flex-wrap">
            <div>
                <strong>{{ $a->student->name }}</strong>
                @if ($a->student->studentProfile?->formation)
                    <span class="text-muted small">· {{ $a->student->studentProfile->formation->intitule }}</span>
                @endif
                <div class="text-muted small">a postulé à <a href="{{ route('offres.afficher', $a->offer) }}">{{ $a->offer->titre }}</a> · {{ $a->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <span class="badge bg-{{ ['pending'=>'warning','accepted'=>'success','rejected'=>'danger','withdrawn'=>'secondary'][$a->status] }} align-self-start">{{ $a->status }}</span>
        </div>
        @if ($a->message)<p class="mt-2 mb-0">{{ $a->message }}</p>@endif

        @if ($a->status === 'pending')
            <form method="POST" action="{{ route('candidatures.decider', $a) }}" class="row g-2 mt-3">@csrf
                <div class="col-md-8"><input class="form-control form-control-sm" name="decision_comment" placeholder="Commentaire (facultatif)"></div>
                <div class="col-md-4 d-flex gap-2">
                    <button name="decision" value="accept" class="btn btn-sm btn-success">Accepter</button>
                    <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">Refuser</button>
                </div>
            </form>
        @elseif ($a->stage)
            <div class="mt-2"><a href="{{ route('stages.afficher', $a->stage) }}" class="btn btn-sm btn-outline-brand">Voir le stage →</a></div>
        @endif
    </div>
@empty
    <p class="text-muted">Aucune candidature.</p>
@endforelse
{{ $candidatures->links() }}
@endsection
