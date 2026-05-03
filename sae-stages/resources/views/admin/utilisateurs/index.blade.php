@extends('layouts.app')
@section('title', 'Admin · Utilisateurs')
@section('content')
<h2 class="mb-3">Utilisateurs</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input class="form-control" name="q" placeholder="Recherche" value="{{ request('q') }}"></div>
    <div class="col-md-3">
        <select name="role" class="form-select">
            <option value="">Tous les rôles</option>
            @foreach (\App\Models\Utilisateur::ROLES as $r)
                <option value="{{ $r }}" @selected(request('role')===$r)>{{ $r }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Tous statuts</option>
            <option value="pending" @selected(request('status')==='pending')>En attente</option>
            <option value="active" @selected(request('status')==='active')>Actifs</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filtrer</button></div>
</form>

<div class="card">
    <table class="table mb-0">
        <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Actif</th><th>Inscrit</th><th></th></tr></thead>
        <tbody>
            @foreach ($utilisateurs as $u)
                @php $fid = 'fu'.$u->id; @endphp
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <select form="{{ $fid }}" name="role" class="form-select form-select-sm">
                            @foreach (\App\Models\Utilisateur::ROLES as $r)
                                <option value="{{ $r }}" @selected($u->role===$r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select form="{{ $fid }}" name="is_active" class="form-select form-select-sm">
                            <option value="1" @selected($u->is_active)>Oui</option>
                            <option value="0" @selected(!$u->is_active)>Non</option>
                        </select>
                    </td>
                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                    <td><button form="{{ $fid }}" class="btn btn-sm btn-primary">Sauvegarder</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@foreach ($utilisateurs as $u)
    <form id="fu{{ $u->id }}" method="POST" action="{{ route('admin.utilisateurs.maj', $u) }}">@csrf @method('PUT')</form>
@endforeach
<div class="mt-3">{{ $utilisateurs->links() }}</div>
@endsection
