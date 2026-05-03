@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-bell"></i> Notifications</h2>
    @if ($notifications->whereNull('read_at')->count())
        <form method="POST" action="{{ route('notifications.tout_lire') }}">@csrf
            <button class="btn btn-sm btn-outline-brand">Tout marquer comme lu</button>
        </form>
    @endif
</div>

<div class="card">
@forelse ($notifications as $n)
    <div class="p-3 border-bottom d-flex justify-content-between align-items-start {{ $n->read_at ? '' : 'bg-light' }}">
        <div>
            <strong>{{ $n->title }}</strong>
            @if ($n->message)<div class="small text-muted">{{ $n->message }}</div>@endif
            <div class="small text-muted">{{ $n->created_at->diffForHumans() }} · {{ $n->type }}</div>
        </div>
        <div>
            @if (! $n->read_at)
                <form method="POST" action="{{ route('notifications.lire', $n) }}">@csrf
                    <button class="btn btn-sm btn-outline-brand">{{ $n->url ? 'Ouvrir' : 'Marquer lue' }}</button>
                </form>
            @elseif ($n->url)
                <a href="{{ $n->url }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
            @endif
        </div>
    </div>
@empty
    <p class="text-muted text-center py-4 mb-0">Aucune notification.</p>
@endforelse
</div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
