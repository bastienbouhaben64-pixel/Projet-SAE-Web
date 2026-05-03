@extends('layouts.app')
@section('title', 'Mon compte')
@section('content')
<h2 class="mb-3"><i class="bi bi-person-circle"></i> Mon compte</h2>
@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif
@include('profil._compte')
@endsection
