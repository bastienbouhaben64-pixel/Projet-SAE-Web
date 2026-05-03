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
<body>
<nav class="navbar-cy">
    <div class="container-xl d-flex align-items-center flex-wrap gap-2">
        <a class="brand" href="{{ route('accueil') }}">
            <img src="{{ asset('img/cy-tech.png') }}" alt="CY Tech" height="32" class="me-2">
            <span class="brand-suffix">SAE Stages</span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            @auth
                <a class="nav-link {{ request()->routeIs('tableau_bord') ? 'active' : '' }}" href="{{ route('tableau_bord') }}">Tableau de bord</a>
                <a class="nav-link {{ request()->routeIs('offres.*') ? 'active' : '' }}" href="{{ route('offres.index') }}">Offres</a>
                @if (auth()->user()->isEntreprise() || auth()->user()->isAdmin())
                    <a class="nav-link" href="{{ route('offres.miennes') }}">Mes offres</a>
                @endif
                @php $unread = auth()->user()->notifications()->whereNull('read_at')->count(); @endphp
                <a href="{{ route('notifications.index') }}" class="position-relative me-2 text-secondary" title="Notifications">
                    <i class="bi bi-bell notification-icon"></i>
                    @if ($unread)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">{{ $unread }}</span>
                    @endif
                </a>
                <span class="role-badge ms-2">{{ auth()->user()->role }}</span>
                <a href="{{ route('profil.afficher') }}" class="text-decoration-none text-muted small d-none d-md-inline" title="Mon profil">
                    <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                </a>
                <form method="POST" action="{{ route('deconnexion') }}">@csrf
                    <button class="btn btn-sm btn-outline-secondary">Déconnexion</button>
                </form>
            @else
                <a class="nav-link" href="{{ route('accueil') }}#offres">Offres</a>
                <a class="nav-link" href="{{ route('accueil') }}#espaces">Espaces</a>
                <a class="nav-link" href="{{ route('accueil') }}#gantt">Gantt</a>
                <a class="btn btn-sm btn-brand px-3 ms-2" href="{{ route('connexion') }}">Connexion</a>
            @endauth
        </div>
    </div>
</nav>

<div class="container-xl py-4">
    <div class="row g-4">
        @auth
            <div class="col-12 d-lg-none mb-2">
                <button class="btn btn-sm btn-outline-brand w-100" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMobile" aria-expanded="false" aria-controls="sidebarMobile">
                    <i class="bi bi-list"></i> Menu
                </button>
            </div>
            <aside class="col-lg-2">
                <div class="bg-white p-3 rounded border sidebar collapse d-lg-block" id="sidebarMobile">
                    @includeIf('partials.sidebar-'.auth()->user()->role)
                </div>
            </aside>
            <main class="col-lg-10">
        @else
            <main class="col-12">
        @endauth

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @yield('content')
        </main>
    </div>
</div>

<footer class="text-center text-muted py-4 small">
    SAE Suivi Stages — CY Tech · Phase 1
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
