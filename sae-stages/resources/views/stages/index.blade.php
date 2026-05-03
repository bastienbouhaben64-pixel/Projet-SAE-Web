@extends('layouts.app')
@section('title', 'Stages')
@section('content')
<h2 class="mb-3"><i class="bi bi-briefcase"></i> Stages</h2>
<div class="card">
<table class="table mb-0">
    <thead><tr><th>Offre</th><th>Étudiant</th><th>Entreprise</th><th>Tuteur</th><th>Période</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    @forelse ($stages as $s)
        <tr>
            <td>{{ $s->offer->titre }}</td>
            <td>{{ $s->student->name }}</td>
            <td>{{ optional($s->company->companyProfile)->raison_sociale ?? $s->company->name }}</td>
            <td>{{ $s->tutor?->name ?? '—' }}</td>
            <td class="small">{{ $s->date_debut->format('d/m/Y') }} → {{ $s->date_fin->format('d/m/Y') }}</td>
            <td><span class="badge bg-{{ ['brouillon'=>'secondary','convention'=>'warning','en_cours'=>'info','termine'=>'primary','valide'=>'success'][$s->status] }}">{{ str_replace('_',' ',$s->status) }}</span></td>
            <td><a href="{{ route('stages.afficher', $s) }}" class="btn btn-sm btn-outline-brand">Ouvrir →</a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Aucun stage.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $stages->links() }}</div>
@endsection
