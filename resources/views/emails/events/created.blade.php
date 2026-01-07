@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Ciao {{ $event->community->name }},</h1>
    
    <p>Abbiamo ricevuto la richiesta per il tuo nuovo evento:</p>
    
    <h2 style="color: #f19cba; margin: 10px 0;">{{ $event->title }}</h2>

    <p>Ti informiamo che il tuo evento è stoto creato correttamente ed è ora <b>in attesa di approvazione</b> da parte del nostro team.</p>
    
    <p>Riceverai una nuova notifica non appena sarà pubblicato ufficialmente.</p>

    <div style="text-align: center;">
        <a href="{{ url('/dashboard/communities/events') }}" class="button">Vai alla Dashboard</a>
    </div>
@endsection