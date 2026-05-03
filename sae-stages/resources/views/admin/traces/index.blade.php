@extends('layouts.app')
@section('title', 'Admin · Traces')
@section('content')
<h2 class="mb-3">Journal d'activité</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input class="form-control" name="action" placeholder="Action (ex: login)" value="{{ request('action') }}"></div>
    <div class="col-md-2"><input class="form-control" name="user_id" placeholder="User ID" value="{{ request('user_id') }}"></div>
    <div class="col-md-3"><input class="form-control" type="date" name="from" value="{{ request('from') }}"></div>
    <div class="col-md-3"><input class="form-control" type="date" name="to" value="{{ request('to') }}"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">OK</button></div>
</form>
<div class="card">
    <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Date</th><th>Action</th><th>Utilisateur</th><th>IP</th><th class="trace-payload-col">Payload</th></tr></thead>
        <tbody>
            @foreach ($traces as $l)
                <tr>
                    <td class="small text-nowrap">{{ $l->created_at->format('Y-m-d H:i:s') }}</td>
                    <td><code>{{ $l->action }}</code></td>
                    <td class="text-break">{{ $l->user?->email ?? '—' }}</td>
                    <td class="small">{{ $l->ip }}</td>
                    <td class="small trace-payload"><code>{{ json_encode($l->payload, JSON_UNESCAPED_UNICODE) }}</code></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3 pagination-wrap">{{ $traces->links() }}</div>
<p class="text-muted small mt-2 mb-0">Fichier miroir : <code>storage/logs/trace-YYYY-MM-DD.log</code></p>
@endsection
