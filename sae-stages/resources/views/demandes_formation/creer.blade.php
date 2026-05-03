@extends('layouts.app')
@section('title', 'Demande de formation')
@section('content')
<h2 class="mb-3">Demander l'ajout d'une formation</h2>
<form method="POST" action="{{ route('demandes_formation.envoyer') }}" class="card p-3">
    @csrf
    <div class="mb-3">
        <label class="form-label">Intitulé de la formation</label>
        <input class="form-control" name="intitule" required value="{{ old('intitule') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Justification (facultatif)</label>
        <textarea class="form-control" rows="5" name="justification">{{ old('justification') }}</textarea>
    </div>
    <button class="btn btn-primary">Envoyer la demande</button>
    <a href="{{ route('demandes_formation.miennes') }}" class="btn btn-link">Mes demandes</a>
</form>
@endsection
