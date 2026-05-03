@extends('layouts.app')
@section('title', 'Stage — '.$stage->offer->titre)
@section('content')
@php
    $u = auth()->user();
    $isStudent = $u->id === $stage->student_id;
    $isCompany = $u->id === $stage->company_id;
    $isTutor = $u->id === $stage->tutor_id;
    $isJury = $u->isJury();
    $isAdmin = $u->isAdmin();
    $statuses = ['brouillon','convention','en_cours','termine','valide'];
    $currentIdx = array_search($stage->status, $statuses);
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h2 class="mb-1">{{ $stage->offer->titre }}</h2>
        <div class="text-muted small">
            <i class="bi bi-building"></i> {{ optional($stage->company->companyProfile)->raison_sociale ?? $stage->company->name }}
            · <i class="bi bi-person"></i> {{ $stage->student->name }}
            · <i class="bi bi-mortarboard"></i> Tuteur : {{ $stage->tutor?->name ?? 'non affecté' }}
            · <i class="bi bi-calendar"></i> {{ $stage->date_debut->format('d/m/Y') }} → {{ $stage->date_fin->format('d/m/Y') }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stages.pdf', $stage) }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-file-earmark-pdf"></i> Dossier PDF</a>
        <a href="{{ route('stages.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
    </div>
</div>

<div class="card p-3 mb-3">
    <div class="timeline">
        @foreach ($statuses as $i => $s)
            <div class="step {{ $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'active' : '') }}">
                {{ ucfirst(str_replace('_',' ',$s)) }}
            </div>
        @endforeach
    </div>
    @if (in_array($stage->status, ['en_cours','termine','valide']))
        <div class="progress mt-3 progress-stage">
            <div class="progress-bar progress-bar-cy" style="width: {{ $stage->progressPercent() }}%"></div>
        </div>
        <div class="text-muted small mt-1">{{ $stage->progressPercent() }} % de la période écoulée</div>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card p-3 mb-3">
            <h5 class="mb-2"><i class="bi bi-file-earmark-text"></i> Convention</h5>
            <a href="{{ route('conventions.afficher', $stage) }}" class="btn btn-sm btn-outline-brand">Voir / signer</a>
        </div>

        <div class="card p-3 mb-3">
            <h5 class="d-flex justify-content-between"><span><i class="bi bi-folder"></i> Documents</span></h5>
            @forelse ($stage->documents as $d)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <span class="badge bg-info">{{ $d->type }}</span>
                        <strong>{{ $d->titre }}</strong>
                        <span class="text-muted small">par {{ $d->uploader->name }} · {{ $d->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <a href="{{ route('documents.telecharger', $d) }}" class="btn btn-sm btn-outline-brand">Télécharger</a>
                        @if ($isAdmin || $d->uploaded_by === $u->id)
                            <form method="POST" action="{{ route('documents.supprimer', $d) }}" class="d-inline" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">×</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">Aucun document.</p>
            @endforelse
            @if ($isAdmin || $isStudent || $isTutor || $isCompany)
                <form method="POST" action="{{ route('documents.envoyer', $stage) }}" enctype="multipart/form-data" class="row g-2 mt-3">@csrf
                    <div class="col-4">
                        <select class="form-select form-select-sm" name="type">
                            <option value="rapport">Rapport</option>
                            <option value="resume">Résumé</option>
                            <option value="fiche_eval">Fiche éval.</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="col-8"><input class="form-control form-control-sm" name="titre" placeholder="Titre" required></div>
                    <div class="col-9"><input type="file" class="form-control form-control-sm" name="file" required></div>
                    <div class="col-3"><button class="btn btn-sm btn-brand w-100">Déposer</button></div>
                </form>
            @endif
        </div>

        <div class="card p-3 mb-3">
            <h5><i class="bi bi-list-check"></i> Missions</h5>
            @foreach ($stage->missions as $m)
                <div class="py-2 border-bottom">
                    <strong>{{ $m->titre }}</strong>
                    <span class="badge bg-{{ ['todo'=>'secondary','in_progress'=>'warning','done'=>'success'][$m->status] }}">{{ $m->status }}</span>
                    @if ($m->due_date)<span class="text-muted small">· échéance {{ $m->due_date->format('d/m/Y') }}</span>@endif
                    @if ($m->description)<div class="small">{{ $m->description }}</div>@endif
                    @if ($isStudent || $isCompany || $isAdmin)
                        <form method="POST" action="{{ route('missions.maj', $m) }}" class="d-inline-block mt-1">@csrf @method('PUT')
                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                @foreach (['todo','in_progress','done'] as $s)
                                    <option value="{{ $s }}" @selected($m->status===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            @endforeach
            @if ($isCompany || $isAdmin)
                <form method="POST" action="{{ route('missions.creer', $stage) }}" class="row g-2 mt-3">@csrf
                    <div class="col-md-5"><input class="form-control form-control-sm" name="titre" placeholder="Titre de la mission" required></div>
                    <div class="col-md-4"><input class="form-control form-control-sm" name="description" placeholder="Description"></div>
                    <div class="col-md-2"><input type="date" class="form-control form-control-sm" name="due_date"></div>
                    <div class="col-md-1"><button class="btn btn-sm btn-brand w-100">+</button></div>
                </form>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        @if ($isStudent || $isAdmin)
            <div class="card p-3 mb-3">
                <h5><i class="bi bi-journal-text"></i> Cahier de stage</h5>
                @forelse ($stage->cahierEntries as $e)
                    <div class="py-2 border-bottom">
                        <div class="d-flex justify-content-between"><strong>{{ $e->titre }}</strong><span class="text-muted small">{{ $e->date->format('d/m/Y') }}</span></div>
                        <p class="mb-0 small">{{ $e->contenu }}</p>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Aucune entrée.</p>
                @endforelse
                <form method="POST" action="{{ route('cahier.ajouter', $stage) }}" class="mt-3">@csrf
                    <div class="row g-2">
                        <div class="col-md-3"><input type="date" class="form-control form-control-sm" name="date" value="{{ now()->toDateString() }}" required></div>
                        <div class="col-md-9"><input class="form-control form-control-sm" name="titre" placeholder="Titre du jour" required></div>
                        <div class="col-12"><textarea class="form-control form-control-sm" name="contenu" rows="2" placeholder="Activités, apprentissages, blocages…" required></textarea></div>
                        <div class="col-12 text-end"><button class="btn btn-sm btn-brand">Ajouter au cahier</button></div>
                    </div>
                </form>
            </div>
        @endif

        <div class="card p-3 mb-3">
            <h5><i class="bi bi-chat-left-text"></i> Remarques</h5>
            @forelse ($stage->remarks as $r)
                <div class="py-2 border-bottom">
                    <div class="small text-muted">{{ $r->author->name }} ({{ $r->author_role }}) · {{ $r->created_at->diffForHumans() }} · {{ $r->scope }}</div>
                    <div>{{ $r->contenu }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">Aucune remarque.</p>
            @endforelse
            <form method="POST" action="{{ route('stages.remarques.ajouter', $stage) }}" class="mt-3">@csrf
                <div class="row g-2">
                    <div class="col-md-3">
                        <select name="scope" class="form-select form-select-sm">
                            <option value="general">Générale</option>
                            <option value="rapport">Sur le rapport</option>
                        </select>
                    </div>
                    <div class="col-md-9"><input class="form-control form-control-sm" name="contenu" placeholder="Votre remarque…" required></div>
                    <div class="col-12 text-end"><button class="btn btn-sm btn-outline-brand">Ajouter</button></div>
                </div>
            </form>
        </div>

        <div class="card p-3 mb-3">
            <h5>Actions</h5>
            @if ($isStudent && $stage->status === 'en_cours')
                <form method="POST" action="{{ route('stages.terminer', $stage) }}">@csrf
                    <button class="btn btn-sm btn-warning">Marquer comme terminé</button>
                </form>
            @endif
            @if (($isJury || $isAdmin) && $stage->status === 'termine')
                <form method="POST" action="{{ route('stages.valider_jury', $stage) }}" class="mt-2">@csrf
                    <h6 class="mb-2"><i class="bi bi-clipboard-check"></i> Évaluation jury</h6>
                    <div class="row g-2 mb-2">
                        @foreach (\App\Models\Stage::CRITERES_JURY as $k => $libelle)
                            <div class="col-12 d-flex align-items-center gap-2">
                                <label class="small flex-grow-1">{{ $libelle }}</label>
                                <select name="criteres[{{ $k }}]" class="form-select form-select-sm criteria-select">
                                    <option value="">—</option>
                                    @for ($i=0; $i<=5; $i++)<option value="{{ $i }}">{{ $i }}/5</option>@endfor
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Note globale /20 <span class="text-muted">(auto si laissée vide)</span></label>
                        <input type="number" step="0.25" min="0" max="20" class="form-control form-control-sm" name="jury_note">
                    </div>
                    <textarea class="form-control form-control-sm mb-2" name="jury_comment" placeholder="Commentaire du jury (facultatif)" rows="2"></textarea>
                    <button class="btn btn-sm btn-success w-100"><i class="bi bi-check2-circle"></i> Valider le stage (jury)</button>
                </form>
            @endif
            @if ($stage->status === 'valide')
                <div class="alert alert-success mb-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            Stage validé{{ $stage->validated_at ? ' le '.$stage->validated_at->format('d/m/Y') : '' }} par {{ $stage->jury?->name }}.
                        </div>
                        @if ($stage->jury_note !== null)
                            <span class="badge bg-success fs-6">{{ rtrim(rtrim(number_format($stage->jury_note, 2, ',', ''), '0'), ',') }}/20</span>
                        @endif
                    </div>
                    @if ($stage->jury_grille)
                        <ul class="small mt-2 mb-1 ps-3">
                            @foreach (\App\Models\Stage::CRITERES_JURY as $k => $libelle)
                                @if (isset($stage->jury_grille[$k]))
                                    <li>{{ $libelle }} : <strong>{{ $stage->jury_grille[$k] }}/5</strong></li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                    @if ($stage->jury_comment)<em class="small">« {{ $stage->jury_comment }} »</em>@endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
