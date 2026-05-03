@extends('layouts.app')
@section('title', 'Mes candidatures')
@section('content')
<h2 class="mb-3"><i class="bi bi-send"></i> Mes candidatures</h2>
<div class="card">
<table class="table mb-0">
    <thead><tr><th>Offre</th><th>Entreprise</th><th>Statut</th><th>Stage</th><th>Envoyée</th><th></th></tr></thead>
    <tbody>
    @forelse ($candidatures as $a)
        <tr>
            <td><a href="{{ route('offres.afficher', $a->offer) }}">{{ $a->offer->titre }}</a></td>
            <td>{{ optional($a->offer->company->companyProfile)->raison_sociale ?? $a->offer->company->name }}</td>
            <td>
                <span class="badge bg-{{ ['pending'=>'warning','accepted'=>'success','rejected'=>'danger','withdrawn'=>'secondary'][$a->status] }}">
                    {{ $a->status }}
                </span>
                @if ($a->decision_comment) <div class="small text-muted">« {{ $a->decision_comment }} »</div> @endif
            </td>
            <td>
                @if ($a->stage)
                    <a href="{{ route('stages.afficher', $a->stage) }}" class="btn btn-sm btn-outline-brand">Voir mon stage →</a>
                @else — @endif
            </td>
            <td>{{ $a->created_at->format('d/m/Y') }}</td>
            <td>
                @if ($a->status === 'pending')
                    <form method="POST" action="{{ route('candidatures.retirer', $a) }}">@csrf @method('PUT')
                        <button class="btn btn-sm btn-outline-secondary">Retirer</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Aucune candidature.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $candidatures->links() }}</div>
@endsection
