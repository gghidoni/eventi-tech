<?php

return [
    'title' => 'Dashboard',
    'bookmarks' => [
        'title' => 'Eventi preferiti',
        'heading' => 'i miei eventi preferiti',
        'empty' => 'Nessun evento preferito',
    ],
    'cards' => [
        'favorites' => [
            'events' => 'eventi',
            'label' => 'preferiti',
        ],
        'pending_events' => [
            'label' => 'in fase di approvazione',
        ],
        'active_events' => [
            'label' => 'attivi',
        ],
    ],
    'links' => [
        'my_favorites' => 'i miei eventi preferiti',
        'all_my_events' => 'tutti i miei eventi',
        'create_event' => 'crea nuovo evento',
        'my_communities' => 'le tue communities',
    ],
    'events' => [
        'edit_title' => 'Modifica evento',
        'create_title' => 'Crea evento',
        'success_created' => 'Evento creato con successo, attendere l\'approvazione',
        'success_updated' => 'Evento modificato con successo',
        'error_update' => 'Errore durante la modifica dell\'evento',
        'select_community' => 'scegli una community',
        'fields' => [
            'title' => 'titolo',
            'description' => 'descrizione',
            'type' => 'tipo',
            'start' => 'inizio',
            'end' => 'fine',
            'address' => 'indirizzo',
            'city' => 'città',
            'city_placeholder' => 'inserisci la città',
            'website' => 'sito web',
            'tickets_url' => 'tickets url',
            'cfp_url' => 'cfp url',
            'poster' => 'locandina',
            'change_image' => 'Cambia immagine',
            'select_file' => 'Seleziona un file',
            'no_poster' => 'no locandina',
            'uploading' => 'caricamento immagine...',
        ],
    ],
    'communities' => [
        'create_title' => 'crea una community',
        'success_created' => 'Community creata con successo, attendi l\'approvazione',
        'error_create' => 'Errore durante la creazione della community',
    ],
];
