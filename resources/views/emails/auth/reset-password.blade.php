@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-bottom: 20px;">Reimposta la tua password</h1>

    <p>Ciao {{ $user->name }}, hai richiesto di reimpostare la password del tuo account su {{ config('app.name') }}.</p>

    <p>Clicca sul pulsante qui sotto per scegliere una nuova password:</p>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ $url }}" class="button">
            Reimposta password
        </a>
    </div>

    <p style="font-size: 14px; color: #737373;">
        Questo link scadrà tra {{ config('auth.passwords.users.expire', 60) }} minuti.
    </p>

    <p style="font-size: 14px; color: #737373;">
        Se non hai richiesto tu il reset della password, puoi ignorare questa email.
    </p>

    <hr style="border: none; border-top: 1px solid #404040; margin: 20px 0;">

    <p style="font-size: 12px; color: #737373;">
        Se hai problemi con il pulsante, copia e incolla questo link nel tuo browser:<br>
        <span style="color: #72b7c0; word-break: break-all;">{{ $url }}</span>
    </p>
@endsection
