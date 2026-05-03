<x-mail::message>
# {{ $titre }}

@if ($destinataire)
Bonjour **{{ $destinataire }}**,
@else
Bonjour,
@endif

@if ($contenu)
{!! nl2br(e($contenu)) !!}
@else
Vous avez une nouvelle notification sur SAE Stages.
@endif

@if ($url)
<x-mail::button :url="$url">
Consulter
</x-mail::button>
@endif

Cordialement,
{{ config('app.name') }}
</x-mail::message>
