@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Stato candidatura aggiornato</h1>

    <p>La tua candidatura ha cambiato stato.</p>

    <h2 style="color: #f19cba; margin: 10px 0;">{{ $submission->title }}</h2>

    <p><strong>Evento:</strong> {{ $submission->cfp->event->title }}</p>
    <p><strong>Nuovo stato:</strong> {{ $statusLabel }}</p>

    <div style="text-align: center;">
        <a href="{{ route('dashboard.cfp-submissions') }}" class="button">Le mie candidature</a>
    </div>
@endsection
