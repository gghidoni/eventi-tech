@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Nuovo evento da approvare</h1>
    
    <p>È stato creato un nuovo evento che richiede la tua approvazione:</p>
    
    <h2 style="color: #f19cba; margin: 10px 0;">{{ $event->title }}</h2>

    <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #f19cba; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>Community:</strong> {{ $event->community->name }}</p>
        <p style="margin: 5px 0;"><strong>Tipo:</strong> {{ trans('titles.event.type.' . $event->type->value) }}</p>
        <p style="margin: 5px 0;"><strong>Data inizio:</strong> {{ $event->formatted_datetime_start }}</p>
        <p style="margin: 5px 0;"><strong>Creato da:</strong> {{ $event->community->user->name }} ({{ $event->community->user->email }})</p>
    </div>

    <div style="text-align: center;">
        <a href="{{ url('/admin/events/' . $event->id) }}" class="button">Vai al pannello Filament</a>
    </div>
@endsection
