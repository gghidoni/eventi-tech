<?php

return [
    'title'            => 'Profilo',
    'heading'          => 'modifica profilo',
    'section_profile'  => 'dati personali',
    'section_password' => 'cambia password',

    'fields' => [
        'name'             => 'nome',
        'avatar'           => 'avatar',
        'select_file'      => 'Seleziona un file',
        'change_image'     => 'Cambia immagine',
        'uploading'        => 'caricamento immagine...',
        'current_password' => 'password attuale',
        'new_password'     => 'nuova password',
        'confirm_password' => 'conferma password',
    ],

    'actions' => [
        'save_profile'  => 'salva profilo',
        'save_password' => 'aggiorna password',
    ],

    'success_profile'  => 'Profilo aggiornato con successo!',
    'success_password'  => 'Password aggiornata con successo!',
    'error_profile'    => 'Errore durante il salvataggio del profilo',
];
