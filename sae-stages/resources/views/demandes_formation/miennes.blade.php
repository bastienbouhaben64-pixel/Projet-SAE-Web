@extends('layouts.app')
@section('title', 'Mes demandes de formation')
@section('content')
<h2 class="mb-3">Mes demandes de formation</h2>
<a href="{{ route('demandes_formation.creer') }}" class="btn btn-primary mb-3">Nouvelle demande</a>
<div class="card">
    <table class="table mb-0">
        <thead><tr><th>Intitulé</th><th>Statut</th><th>Commentaire admin</th><th>Envoyée le</th></tr></thead>
        <tbody>
            @forelse ($demandes as $r)
                <tr>
                    <td>{{ $r->intitule }}</td>
                    <td><span class="badge bg-{{ $r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'warning') }}">{{ $r->status }}</span></td>
                    <td>{{ $r->admin_comment }}</td>
                    <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Aucune demande.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $demandes->links() }}</div>
@endsection
