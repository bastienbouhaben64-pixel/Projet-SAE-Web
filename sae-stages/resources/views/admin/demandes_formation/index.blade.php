@extends('layouts.app')
@section('title', 'Admin · Demandes formation')
@section('content')
<h2 class="mb-3">Demandes d'ajout de formation</h2>
@forelse ($demandes as $r)
    <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between">
            <div>
                <strong>{{ $r->intitule }}</strong>
                <span class="badge bg-{{ $r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'warning') }}">{{ $r->status }}</span>
                <div class="text-muted small">par {{ $r->user->name }} · {{ $r->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        @if ($r->justification)<p class="mt-2 mb-0">{{ $r->justification }}</p>@endif

        @if ($r->status === 'pending')
            <form method="POST" action="{{ route('admin.demandes_formation.decider', $r) }}" class="row g-2 mt-3">
                @csrf
                <div class="col-md-3"><input class="form-control form-control-sm" name="code" placeholder="Code formation (auto)"></div>
                <div class="col-md-5"><input class="form-control form-control-sm" name="admin_comment" placeholder="Commentaire (optionnel)"></div>
                <div class="col-md-4 d-flex gap-2">
                    <button name="decision" value="approve" class="btn btn-sm btn-success">Approuver</button>
                    <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">Rejeter</button>
                </div>
            </form>
        @else
            <div class="mt-2 small text-muted">
                Traitée le {{ optional($r->handled_at)->format('d/m/Y H:i') }}
                @if ($r->admin_comment) — « {{ $r->admin_comment }} » @endif
            </div>
        @endif
    </div>
@empty
    <p class="text-muted">Aucune demande.</p>
@endforelse
{{ $demandes->links() }}
@endsection
