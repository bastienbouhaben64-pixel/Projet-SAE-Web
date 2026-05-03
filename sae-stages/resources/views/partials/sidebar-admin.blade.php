<div class="small-title fw-semibold text-uppercase mb-2">Admin</div>
<a href="{{ route('tableau_bord') }}"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<a href="{{ route('admin.utilisateurs.index') }}" class="{{ request()->routeIs('admin.utilisateurs.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Utilisateurs</a>
<a href="{{ route('admin.entreprises.index') }}" class="{{ request()->routeIs('admin.entreprises.*') ? 'active' : '' }}"><i class="bi bi-building-check"></i> Entreprises</a>
<a href="{{ route('admin.formations.index') }}" class="{{ request()->routeIs('admin.formations.*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark"></i> Formations</a>
<a href="{{ route('admin.demandes_formation.index') }}" class="{{ request()->routeIs('admin.demandes_formation.*') ? 'active' : '' }}"><i class="bi bi-inbox"></i> Demandes formation</a>
<a href="{{ route('admin.stages.index') }}" class="{{ request()->routeIs('admin.stages.*') ? 'active' : '' }}"><i class="bi bi-briefcase"></i> Stages (affectation)</a>
<a href="{{ route('offres.index') }}"><i class="bi bi-search"></i> Offres</a>
<a href="{{ route('gantt') }}"><i class="bi bi-bar-chart-steps"></i> Gantt</a>
<a href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Notifications</a>
<a href="{{ route('admin.traces.index') }}" class="{{ request()->routeIs('admin.traces.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data"></i> Traces</a>
<a href="{{ route('admin.parametres.edit') }}" class="{{ request()->routeIs('admin.parametres.*') ? 'active' : '' }}"><i class="bi bi-gear"></i> Paramètres</a>
<a href="{{ route('profil.afficher') }}"><i class="bi bi-person-badge"></i> Mon profil</a>
