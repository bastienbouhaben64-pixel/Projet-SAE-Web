<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SAE Suivi Stages')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('img/cy-tech.png') }}" alt="CY Tech" height="46">
        </div>

        @if (session('status'))
            <div class="alert alert-success py-2">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')

        <div class="text-center auth-links mt-3">
            @yield('links')
            <a href="{{ route('accueil') }}">Retour à l'accueil</a>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
