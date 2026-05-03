@extends('layouts.app')
@section('title', 'Plateforme de Suivi de Stages')
@section('content')
<section id="offres" class="hero mb-5">
    <h1>Plateforme de Suivi de Stages</h1>
    <p class="lead">Simplifiez vos démarches, validez vos conventions et suivez vos missions en temps réel.</p>
    <form action="{{ route('offres.index') }}" method="GET" class="search-bar">
        <input type="text" name="q" placeholder="Rechercher une offre (titre, lieu, durée…)">
        <button class="btn btn-brand">Filtrer</button>
    </form>
</section>

<h2 id="espaces" class="text-center section-title mb-4">Accédez à votre espace dédié</h2>
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="role-card">
            <h5>Étudiant</h5>
            <p>Consultez les offres, déposez vos rapports et suivez votre validation.</p>
            <a class="cta" href="{{ route('connexion', ['role' => 'etudiant']) }}">Entrer →</a>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="role-card">
            <h5>Administrateur</h5>
            <p>Gestion des profils, archivage des dossiers et planification GANTT.</p>
            <a class="cta" href="{{ route('connexion', ['role' => 'admin']) }}">Gérer →</a>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="role-card">
            <h5>Entreprise</h5>
            <p>Déposez vos offres, signez les conventions et évaluez les stagiaires.</p>
            <a class="cta" href="{{ route('connexion', ['role' => 'entreprise']) }}">Publier →</a>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="role-card">
            <h5>Tuteurs &amp; Jurys</h5>
            <p>Suivez l'avancement, validez les stages et consultez les cahiers de stage.</p>
            <a class="cta" href="{{ route('connexion', ['role' => 'professeur']) }}">Tuteur →</a>
            &nbsp;·&nbsp;
            <a class="cta" href="{{ route('connexion', ['role' => 'jury']) }}">Jury →</a>
        </div>
    </div>
</div>

@isset($stats)
<section class="row g-3 mb-5">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="value">{{ $stats['offres'] }}</div>
            <div class="label"><i class="bi bi-briefcase"></i> Offres publiées</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="value">{{ $stats['entreprises'] }}</div>
            <div class="label"><i class="bi bi-building-check"></i> Entreprises partenaires</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="value">{{ $stats['stages_en_cours'] }}</div>
            <div class="label"><i class="bi bi-activity"></i> Stages en cours</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="value">{{ $stats['etudiants'] }}</div>
            <div class="label"><i class="bi bi-mortarboard"></i> Étudiants inscrits</div>
        </div>
    </div>
</section>
@endisset

@isset($offresRecentes)
@if ($offresRecentes->isNotEmpty())
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h3 class="section-title mb-0"><i class="bi bi-stars"></i> Dernières offres</h3>
        <a href="{{ route('offres.index') }}" class="small">Toutes les offres →</a>
    </div>
    <div class="row g-3">
        @foreach ($offresRecentes as $o)
            <div class="col-md-4">
                <div class="offre-mini">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">{{ $o->titre }}</h6>
                        @if ($o->domaine)<span class="badge bg-info">{{ $o->domaine }}</span>@endif
                    </div>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-building"></i> {{ optional($o->company->companyProfile)->raison_sociale ?? $o->company->name }}
                        · <i class="bi bi-geo-alt"></i> {{ $o->lieu }}
                        · <i class="bi bi-calendar"></i> {{ $o->duree_semaines }} sem.
                    </div>
                    <p class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($o->description), 110) }}</p>
                    <a href="{{ route('connexion') }}" class="small fw-semibold">Se connecter pour postuler →</a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
@endisset

<section class="mb-5">
    <h3 class="section-title mb-4 text-center"><i class="bi bi-signpost-2"></i> Comment ça marche ?</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="step mb-3">
                <div class="step-num">1</div>
                <div>
                    <h6 class="mb-1">Créez votre compte</h6>
                    <p class="text-muted small mb-0">Inscription en 2 minutes selon votre profil (étudiant, entreprise, professeur).</p>
                </div>
            </div>
            <div class="step mb-3">
                <div class="step-num">2</div>
                <div>
                    <h6 class="mb-1">Trouvez ou publiez une offre</h6>
                    <p class="text-muted small mb-0">Recherche multi-critères pour les étudiants, formulaire structuré pour les entreprises.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="step mb-3">
                <div class="step-num">3</div>
                <div>
                    <h6 class="mb-1">Signez la convention</h6>
                    <p class="text-muted small mb-0">Workflow de signature numérique étudiant → entreprise → tuteur → administration.</p>
                </div>
            </div>
            <div class="step mb-3">
                <div class="step-num">4</div>
                <div>
                    <h6 class="mb-1">Suivez et validez le stage</h6>
                    <p class="text-muted small mb-0">Cahier de stage, missions, GANTT et évaluation finale par un jury dédié.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="gantt" class="card p-4 mt-5 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h3 class="section-title mb-2"><i class="bi bi-bar-chart-steps"></i> Planification GANTT</h3>
            <p class="text-muted mb-0">Visualisez les jalons de chaque stage : signature de la convention, début, mi-parcours,
                rendu du rapport et soutenance. Disponible pour les administrateurs et tuteurs.</p>
        </div>
        <div class="col-md-5">
            <div class="p-3 gantt-preview">
                @foreach (['Convention' => 20, 'Début de stage' => 35, 'Mi-parcours' => 60, 'Rapport' => 85, 'Soutenance' => 100] as $label => $w)
                    <div class="d-flex align-items-center mb-2 small">
                        <div class="text-muted gantt-preview-label">{{ $label }}</div>
                        <div class="flex-grow-1 gantt-preview-track">
                            <div class="gantt-preview-bar" style="width: {{ $w }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <h3 class="section-title mb-4 text-center"><i class="bi bi-question-circle"></i> Questions fréquentes</h3>
    <div class="row g-3 justify-content-center">
        <div class="col-lg-9">
            <details class="faq-item">
                <summary>Comment publier une offre de stage en tant qu'entreprise ?</summary>
                <div class="answer">Après création de votre compte, votre profil entreprise est validé par l'administration. Vous pouvez ensuite déposer autant d'offres que nécessaire, les modifier, et consulter les candidatures reçues directement depuis votre tableau de bord.</div>
            </details>
            <details class="faq-item">
                <summary>Quel est le processus de signature de convention ?</summary>
                <div class="answer">Une fois la candidature acceptée, la convention est signée successivement par l'étudiant, l'entreprise, le tuteur académique puis validée par l'administration. Chaque étape déclenche une notification (in-app + email).</div>
            </details>
            <details class="faq-item">
                <summary>Comment se passe l'évaluation finale ?</summary>
                <div class="answer">À la fin du stage, le jury accède à une grille d'évaluation à 6 critères (technique, autonomie, communication, intégration, qualité du rapport, soutenance) notés sur 5, et calcule automatiquement la note finale sur 20.</div>
            </details>
            <details class="faq-item">
                <summary>Mes données sont-elles sécurisées ?</summary>
                <div class="answer">Oui : authentification 2FA par OTP, conformité RGPD, traçabilité complète des actions sensibles (validation, archivage, exports). Aucune donnée n'est partagée avec des tiers.</div>
            </details>
        </div>
    </div>
</section>

<section class="text-center py-4 mb-3 cta-soft">
    <h4 class="section-title mb-2">Prêt à démarrer ?</h4>
    <p class="text-muted mb-3">Rejoignez la communauté CY Tech et trouvez votre prochain stage.</p>
    <a href="{{ route('inscription') }}" class="btn btn-brand"><i class="bi bi-person-plus"></i> Créer un compte</a>
    <a href="{{ route('connexion') }}" class="btn btn-outline-brand">Se connecter</a>
</section>
@endsection
