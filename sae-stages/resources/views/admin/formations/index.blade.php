@extends('layouts.app')
@section('title', 'Admin · Formations')
@section('content')
<h2 class="mb-3">Formations</h2>
<div class="row g-3">
    <div class="col-md-4">
        <form method="POST" action="{{ route('admin.formations.enregistrer') }}" class="card p-3">
            @csrf
            <h5>Ajouter</h5>
            <div class="mb-2"><label class="form-label">Code</label><input class="form-control" name="code" required></div>
            <div class="mb-2"><label class="form-label">Intitulé</label><input class="form-control" name="intitule" required></div>
            <button class="btn btn-primary">Créer</button>
        </form>
    </div>
    <div class="col-md-8">
        <div class="card">
            <table class="table mb-0">
                <thead><tr><th>Code</th><th>Intitulé</th><th>Active</th><th></th></tr></thead>
                <tbody>
                    @foreach ($formations as $f)
                        @php $fid = 'ff'.$f->id; @endphp
                        <tr>
                            <td>{{ $f->code }}</td>
                            <td><input form="{{ $fid }}" class="form-control form-control-sm" name="intitule" value="{{ $f->intitule }}"></td>
                            <td>
                                <select form="{{ $fid }}" class="form-select form-select-sm" name="is_active">
                                    <option value="1" @selected($f->is_active)>Oui</option>
                                    <option value="0" @selected(!$f->is_active)>Non</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <button form="{{ $fid }}" class="btn btn-sm btn-primary">Sauver</button>
                                <button form="ffd{{ $f->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')">Suppr.</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@foreach ($formations as $f)
    <form id="ff{{ $f->id }}" method="POST" action="{{ route('admin.formations.maj', $f) }}">@csrf @method('PUT')</form>
    <form id="ffd{{ $f->id }}" method="POST" action="{{ route('admin.formations.supprimer', $f) }}">@csrf @method('DELETE')</form>
@endforeach
<div class="mt-3">{{ $formations->links() }}</div>
@endsection
