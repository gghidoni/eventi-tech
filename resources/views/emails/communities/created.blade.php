@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Ciao {{ $community->user->name }},</h1>
    
    <p>Abbiamo ricevuto la richiesta per la tua nuova community:</p>
    
    <h2 style="color: #f19cba; margin: 10px 0;">{{ $community->name }}</h2>

    <p>Ti informiamo che la community è stata creata correttamente ed è ora <b>in attesa di approvazione</b> da parte del nostro team.</p>
    
    <p>Riceverai una nuova notifica non appena sarà pubblicata ufficialmente.</p>

    <div style="text-align: center;">
        <a href="{{ url('/dashboard/communities') }}" class="button">Vai alla Dashboard</a>
    </div>
@endsection