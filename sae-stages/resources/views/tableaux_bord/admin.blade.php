@extends('layouts.app')
@section('title', 'Tableau de bord — Admin')
@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Tableau de bord administrateur</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card kpi d-flex flex-row justify-content-between align-items-center">
            <div>
                <div class="label">Utilisateurs</div>
                <div class="value">{{ $nombreUtilisateurs }}</div>
                <div class="sub">{{ $nombreEtudiants }} étudiants · {{ $nombreEntreprises }} entreprises</div>
            </div>
            <i class="bi bi-people icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('admin.utilisateurs.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card kpi d-flex flex-row justify-content-between align-items-center {{ $utilisateursEnAttente ? 'border-warning' : '' }}">
                <div>
                    <div class="label">Comptes inactifs</div>
                    <div class="value">{{ $utilisateursEnAttente }}</div>
                    <div class="sub">à activer</div>
                </div>
                <i class="bi bi-person-exclamation icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('admin.entreprises.index') }}" class="text-decoration-none">
            <div class="card kpi d-flex flex-row justify-content-between align-items-center {{ $entreprisesAValider ? 'border-warning' : '' }}">
                <div>
                    <div class="label">Entreprises à valider</div>
                    <div class="value">{{ $entreprisesAValider }}</div>
                    <div class="sub">{{ $nombreEntreprises - $entreprisesAValider }} validées</div>
                </div>
                <i class="bi bi-building-check icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('admin.demandes_formation.index') }}" class="text-decoration-none">
            <div class="card kpi d-flex flex-row justify-content-between align-items-center {{ $demandesEnAttente ? 'border-warning' : '' }}">
                <div>
                    <div class="label">Demandes formation</div>
                    <div class="value">{{ $demandesEnAttente }}</div>
                    <div class="sub">en attente</div>
                </div>
                <i class="bi bi-inbox icon"></i>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-6">
        <div class="card kpi d-flex flex-row justify-content-between align-items-center">
            <div>
                <div class="label">Offres</div>
                <div class="value">{{ $nombreOffres }}</div>
                <div class="sub">{{ $offresPubliees }} publiées</div>
            </div>
            <i class="bi bi-megaphone icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi d-flex flex-row justify-content-between align-items-center">
            <div>
                <div class="label">Candidatures</div>
                <div class="value">{{ $candidaturesTotal }}</div>
                <div class="sub">{{ $candidaturesEnAttente }} en attente</div>
            </div>
            <i class="bi bi-send icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('admin.stages.index') }}" class="text-decoration-none">
            <div class="card kpi d-flex flex-row justify-content-between align-items-center">
                <div>
                    <div class="label">Stages</div>
                    <div class="value">{{ $stagesTotal }}</div>
                    <div class="sub">{{ $stagesValides }} validés par jury</div>
                </div>
                <i class="bi bi-briefcase icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="label">Conventions signées</div>
                    <div class="value">{{ $tauxSignature }}%</div>
                    <div class="sub">{{ $signeesCompletes }} / {{ $conventionsTotal }}</div>
                </div>
                <i class="bi bi-pen icon"></i>
            </div>
            <div class="progress progress-thin"><div class="progress-bar progress-bar-cy" role="progressbar" style="width: {{ $tauxSignature }}%;"></div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi d-flex flex-row justify-content-between align-items-center">
            <div>
                <div class="label">Note moyenne jury</div>
                <div class="value">{{ $moyenneJury !== null ? $moyenneJury.'/20' : '—' }}</div>
                <div class="sub">{{ $stagesValides }} stages évalués</div>
            </div>
            <i class="bi bi-star-half icon"></i>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kpi d-flex flex-row justify-content-between align-items-center">
            <div>
                <div class="label">Délai signature</div>
                <div class="value">{{ $delaiMoyen !== null ? $delaiMoyen.' j' : '—' }}</div>
                <div class="sub">moyenne étudiant → admin</div>
            </div>
            <i class="bi bi-clock-history icon"></i>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Stages par statut</h5>
            <canvas id="chartStatuts" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Stages créés (6 derniers mois)</h5>
            <canvas id="chartMensuel" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Pipeline candidatures</h5>
            <canvas id="chartPipeline" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Étapes signature convention</h5>
            <canvas id="chartEtapes" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Distribution des notes jury</h5>
            @if ($distributionNotes->sum() === 0)
                <p class="text-muted small mb-0">Aucun stage évalué pour le moment.</p>
            @else
                <canvas id="chartNotes" height="160"></canvas>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Top lieux d'offres</h5>
            @if ($topLieux->isEmpty())
                <p class="text-muted small mb-0">Aucune offre publiée.</p>
            @else
                <canvas id="chartLieux" height="160"></canvas>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Durée moyenne (semaines) par formation</h5>
            @if ($dureesParFormation->isEmpty())
                <p class="text-muted small mb-0">Aucun stage avec dates.</p>
            @else
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Formation</th><th class="text-end">Sem.</th></tr></thead>
                    <tbody>
                        @foreach ($dureesParFormation as $intitule => $moy)
                            <tr><td>{{ $intitule }}</td><td class="text-end"><strong>{{ $moy }}</strong></td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Top formations</h5>
            @if ($topFormations->isEmpty())
                <p class="text-muted small mb-0">Aucune donnée.</p>
            @else
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Formation</th><th class="text-end">Stages</th></tr></thead>
                    <tbody>
                        @foreach ($topFormations as $f)
                            <tr><td>{{ $f->intitule }}</td><td class="text-end"><strong>{{ $f->n }}</strong></td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Accès rapides</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.utilisateurs.index') }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-people"></i> Utilisateurs</a>
                <a href="{{ route('admin.entreprises.index') }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-building-check"></i> Entreprises</a>
                <a href="{{ route('admin.formations.index') }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-journal-bookmark"></i> Formations</a>
                <a href="{{ route('admin.stages.index') }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-briefcase"></i> Stages</a>
                <a href="{{ route('gantt') }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-bar-chart-steps"></i> Gantt</a>
                <a href="{{ route('admin.traces.index') }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-clipboard-data"></i> Traces</a>
                <a href="{{ route('admin.exports.stages') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export stages</a>
                <a href="{{ route('admin.exports.candidatures') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export candidatures</a>
            </div>
        </div>
    </div>
</div>

<script>
    const palette = ['#94a3b8','#f59e0b','#0ea5e9','#6366f1','#16a34a'];
    const dataStatuts = {!! json_encode($stagesParStatut) !!};
    new Chart(document.getElementById('chartStatuts'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(dataStatuts).map(s => s.replace('_',' ')),
            datasets: [{ data: Object.values(dataStatuts), backgroundColor: palette }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    const serie = {!! json_encode($serieMensuelle->values()) !!};
    new Chart(document.getElementById('chartMensuel'), {
        type: 'bar',
        data: {
            labels: serie.map(p => p.mois),
            datasets: [{ label: 'Stages créés', data: serie.map(p => p.n), backgroundColor: '#2766B0' }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Pipeline candidatures
    const pipeline = {!! json_encode($pipelineCandidatures) !!};
    new Chart(document.getElementById('chartPipeline'), {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'Acceptées', 'Refusées'],
            datasets: [{
                data: [pipeline.pending, pipeline.accepted, pipeline.rejected],
                backgroundColor: ['#f59e0b', '#16a34a', '#dc2626']
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // Étapes signature convention
    const etapes = {!! json_encode($etapesConvention) !!};
    const totalConv = {{ $conventionsTotal ?: 1 }};
    new Chart(document.getElementById('chartEtapes'), {
        type: 'bar',
        data: {
            labels: Object.keys(etapes),
            datasets: [{
                label: '% signataires',
                data: Object.values(etapes).map(n => Math.round(n / totalConv * 100)),
                backgroundColor: ['#2766B0','#1e88e5','#6366f1','#16a34a']
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
        }
    });

    @if ($distributionNotes->sum() > 0)
    // Distribution notes jury
    const notes = {!! json_encode($distributionNotes) !!};
    new Chart(document.getElementById('chartNotes'), {
        type: 'bar',
        data: {
            labels: Object.keys(notes),
            datasets: [{ label: 'Stages', data: Object.values(notes), backgroundColor: '#16a34a' }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    @endif

    @if ($topLieux->isNotEmpty())
    // Top lieux
    const lieux = {!! json_encode($topLieux) !!};
    new Chart(document.getElementById('chartLieux'), {
        type: 'bar',
        data: {
            labels: lieux.map(l => l.lieu),
            datasets: [{ label: 'Offres', data: lieux.map(l => l.n), backgroundColor: '#0ea5e9' }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
    @endif
</script>
@endsection
