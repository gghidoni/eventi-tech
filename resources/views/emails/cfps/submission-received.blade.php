@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Nuova candidatura CFP</h1>

    <p>Hai ricevuto una nuova candidatura per l'evento:</p>

    <h2 style="color: #f19cba; margin: 10px 0;">{{ $submission->cfp->event->title }}</h2>

    <p><strong>Proposta:</strong> {{ $submission->title }}</p>
    <p><strong>Speaker:</strong> {{ $submission->user->name }} ({{ $submission->user->email }})</p>

    <div style="text-align: center;">
        <a href="{{ route('dashboard.cfps.submission', [$submission->cfp, $submission]) }}" class="button">Apri candidatura</a>
    </div>
@endsection
