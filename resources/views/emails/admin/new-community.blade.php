@extends('emails.layout')

@section('content')
    <h1 style="color: #333; font-size: 22px; margin-top: 0;">Nuova community da approvare</h1>
    
    <p>È stata creata una nuova community che richiede la tua approvazione:</p>
    
    <h2 style="color: #f19cba; margin: 10px 0;">{{ $community->name }}</h2>

    <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #f19cba; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>Descrizione:</strong> {{ Str::limit($community->description, 100) }}</p>
        @if($community->website)
            <p style="margin: 5px 0;"><strong>Sito web:</strong> <a href="{{ $community->website }}">{{ $community->website }}</a></p>
        @endif
        <p style="margin: 5px 0;"><strong>Creata da:</strong> {{ $community->user->name }} ({{ $community->user->email }})</p>
    </div>

    <div style="text-align: center;">
        <a href="{{ url('/admin/communities/' . $community->id) }}" class="button">Vai al pannello Filament</a>
    </div>
@endsection
