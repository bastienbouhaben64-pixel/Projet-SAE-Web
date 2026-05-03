@extends('layouts.app')
@section('title', 'Diagramme de Gantt')
@section('content')
<h2 class="mb-3"><i class="bi bi-bar-chart-steps"></i> Diagramme de Gantt</h2>

@if ($stages->isEmpty())
    <div class="card p-4 text-center text-muted">Aucun stage planifié pour le moment.</div>
@else
    @php
        $minTs = strtotime($min);
        $maxTs = strtotime($max);
        $range = max($maxTs - $minTs, 1);
    @endphp
    <div class="card p-3">
        @foreach ($stages as $s)
            @php
                $startTs = $s->date_debut->timestamp;
                $endTs = $s->date_fin->timestamp;
                $left = ($startTs - $minTs) / $range * 100;
                $width = max(($endTs - $startTs) / $range * 100, 1);
            @endphp
            <div class="gantt-row">
                <div class="gantt-label">
                    <a href="{{ route('stages.afficher', $s) }}" class="text-decoration-none">
                        <strong>{{ \Illuminate\Support\Str::limit($s->offer->titre, 35) }}</strong>
                    </a>
                    <div class="text-muted small">{{ $s->student->name }} · {{ $s->tutor?->name ?? 'sans tuteur' }}</div>
                </div>
                <div class="gantt-track">
                    <div class="gantt-bar {{ $s->status }}" style="left: {{ $left }}%; width: {{ $width }}%;">
                        {{ $s->date_debut->format('d/m') }} → {{ $s->date_fin->format('d/m') }} · {{ $s->status }}
                    </div>
                </div>
            </div>
        @endforeach
        <div class="gantt-axis">
            <span>{{ \Carbon\Carbon::parse($min)->format('d/m/Y') }}</span>
            <span>{{ \Carbon\Carbon::parse($max)->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="card p-3 mt-3">
        <h6>Légende</h6>
        <div class="d-flex flex-wrap gap-3 small gantt-legend">
            @foreach (['brouillon','convention','en_cours','termine','valide'] as $st)
                <div><span class="gantt-bar {{ $st }}">{{ $st }}</span></div>
            @endforeach
        </div>
    </div>
@endif
@endsection
