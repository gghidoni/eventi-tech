@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Candidatura inviata</h1>

    <p>La tua candidatura per l'evento seguente e stata inviata correttamente ed e ora in revisione.</p>

    <h2 style="color: #f19cba; margin: 10px 0;">{{ $submission->cfp->event->title }}</h2>

    <p><strong>Proposta:</strong> {{ $submission->title }}</p>

    <div style="text-align: center;">
        <a href="{{ route('dashboard.cfp-submissions') }}" class="button">Le mie candidature</a>
    </div>
@endsection
