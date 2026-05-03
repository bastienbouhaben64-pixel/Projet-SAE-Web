@extends('layouts.app')
@section('title', 'Mes offres')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Mes offres</h2>
    <a href="{{ route('offres.creer') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nouvelle offre</a>
</div>
<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>Titre</th><th>Lieu</th><th>Durée</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($offres as $o)
                <tr>
                    <td><a href="{{ route('offres.afficher', $o) }}">{{ $o->titre }}</a></td>
                    <td>{{ $o->lieu }}</td>
                    <td>{{ $o->duree_semaines }} sem.</td>
                    <td><span class="badge bg-{{ $o->status === 'published' ? 'success' : ($o->status === 'archived' ? 'secondary' : 'warning') }}">{{ $o->status }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('offres.modifier', $o) }}">Modifier</a>
                        @if ($o->status !== 'archived')
                            <form action="{{ route('offres.archiver', $o) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-warning">Archiver</button>
                            </form>
                        @endif
                        <form action="{{ route('offres.supprimer', $o) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Suppr.</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Aucune offre.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $offres->links() }}</div>
@endsection
