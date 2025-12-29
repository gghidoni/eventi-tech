 @extends('emails.layout')

 @section('content')
     <h1 style="color: #333; font-size: 22px; margin-bottom: 20px;">Benvenuto su {{ config('app.name') }}!</h1>

     <p>Ciao {{ $user->name }}, grazie per esserti registrato.</p>

     <p>Per completare la tua iscrizione e iniziare a pubblicare eventi o gestire community, clicca sul pulsante qui sotto
         per verificare il tuo indirizzo email:</p>

     <div style="text-align: center; margin: 35px 0;">
         <a href="{{ $url }}"
             class="button">
             Verifica email
         </a>
     </div>

     <p style="font-size: 14px; color: #737373;">
         Se non hai creato tu questo account, puoi ignorare tranquillamente questa email.
     </p>

     <hr style="border: none; border-top: 1px solid #404040; margin: 20px 0;">

     <p style="font-size: 12px; color: #737373;">
         Se hai problemi con il pulsante, copia e incolla questo link nel tuo browser:<br>
         <span style="color: #72b7c0; word-break: break-all;">{{ $url }}</span>
     </p>
 @endsection
