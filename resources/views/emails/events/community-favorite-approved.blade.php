@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Nuovo evento da {{ $event->community->name }}</h1>

    <p>Una community che segui ha appena pubblicato un nuovo evento:</p>

    <h2 style="color: #f19cba; margin: 10px 0;">{{ $event->title }}</h2>

    <p>Puoi aprire subito la pagina evento oppure gestire i tuoi preferiti dalla dashboard.</p>

    <div style="text-align: center;">
        <a href="{{ $event->public_url }}" class="button">Apri evento</a>
    </div>

    <div style="text-align: center; margin-top: 10px;">
        <a href="{{ url('/dashboard/bookmarks?tab=communities') }}" class="button">Vai ai preferiti</a>
    </div>
@endsection
