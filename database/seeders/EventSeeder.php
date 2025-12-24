<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\AddressBook\AddressBook;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basePosterUrl = 'posters/';
        $events = [
            [
                'community_id' => 1, // ID per "Java Ancona"
                'title'        => 'Java & Spring Boot Workshop',
                'description'  => 'Un workshop pratico per sviluppatori Java. Impara a creare applicazioni scalabili con Spring Boot.',
                'status'       => EventStatus::Active,
                'type'         => EventType::InPerson,
                'start_date'   => now()->addDays(5),
                'end_date'     => now()->addDays(5)->addHours(4),
                'website'      => 'www.javaancora.it',
                'poster'       => 'locandina1.png',
                'tickets_url'  => 'www.javaancora.it/tickets',
                'cfp_url'      => 'www.javaancora.it/cfp',
            ],
            [
                'community_id' => 2, // ID per "Laravel Pordenone"
                'title'        => 'Laravel 10: Nuove Funzionalità e Best Practices, titolo lungo per vedere se si tronca il titolo',
                'description'  => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices. Impara a creare applicazioni scalabili con Laravel.',
                'status'       => EventStatus::Pending,
                'type'         => EventType::Online,
                'start_date'   => now()->addDays(10),
                'end_date'     => now()->addDays(10)->addHours(2),
                'website'      => 'www.laravelprodenone.it',
                'poster'       => 'locandina15.jpg',
                'tickets_url'  => 'www.laravelprodenone.it/tickets',
                'cfp_url'      => 'www.laravelprodenone.it/cfp',
            ],
            [
                'community_id' => 3, // ID per "Wordpress Meetup Firenze"
                'title'        => 'Introduzione a Gutenberg e Blocchi Personalizzati, titolo lungo per vedere se si tronca',
                'description'  => 'Un evento per esplorare la creazione di blocchi personalizzati in Gutenberg per WordPress. Impara a creare contenuti personalizzati con Gutenberg.',
                'status'       => EventStatus::Active,
                'type'         => EventType::Hybrid,
                'start_date'   => now()->addDays(20),
                'end_date'     => now()->addDays(20)->addHours(3),
                'website'      => 'www.wordpressfirenze.it',
                'poster'       => 'locandina12.jpg',
                'tickets_url'  => 'www.wordpressfirenze.it/tickets',
                'cfp_url'      => 'www.wordpressfirenze.it/cfp',
            ],
            [
                'community_id' => 1, // ID per "Java Ancona"
                'title'        => 'PHP 8.1: Novità e Migrazione',
                'description'  => 'Partecipa a questo incontro per scoprire le novità introdotte in PHP 8.1 e come migrare le tue applicazioni.',
                'status'       => EventStatus::Reject,
                'type'         => EventType::InPerson,
                'start_date'   => now()->addDays(15),
                'end_date'     => now()->addDays(15)->addHours(3),
                'website'      => 'www.phpverona.it',
                'poster'       => 'locandina3.jpg',
                'tickets_url'  => 'www.phpverona.it/tickets',
                'cfp_url'      => 'www.phpverona.it/cfp',
            ],
            [
                'community_id' => 2, // ID per "Laravel Pordenone"
                'title'        => 'State Management in React con Redux',
                'description'  => 'Un workshop intensivo su come utilizzare Redux per una gestione avanzata dello stato nelle app React.',
                'status'       => EventStatus::Active,
                'type'         => EventType::Online,
                'start_date'   => now()->addDays(30),
                'end_date'     => now()->addDays(30)->addHours(3),
                'website'      => 'www.reactroma.it',
                'poster'       => 'locandina4.webp',
                'tickets_url'  => 'www.reactroma.it/tickets',
                'cfp_url'      => 'www.reactroma.it/cfp',
            ],
            [
                'community_id' => 3, // ID per "Wordpress Meetup Firenze"
                'title'        => 'Introduzione a Kubernetes e Docker',
                'description'  => 'Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione.',
                'status'       => EventStatus::Terminate,
                'type'         => EventType::Hybrid,
                'start_date'   => now()->addDays(25),
                'end_date'     => now()->addDays(25)->addHours(5),
                'website'      => 'www.devopsnapoli.it',
                'poster'       => null,
                'tickets_url'  => 'www.devopsnapoli.it/tickets',
                'cfp_url'      => 'www.devopsnapoli.it/cfp',
            ],
            [
                'community_id' => 1, // ID per "Java Ancona"
                'title'        => 'Java 17: Novità e Migrazione',
                'description'  => 'Partecipa a questo incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni.',
                'status'       => EventStatus::Active,
                'type'         => EventType::InPerson,
                'start_date'   => now()->addDays(20),
                'end_date'     => now()->addDays(20)->addHours(3),
                'website'      => 'www.javaancora.it',
                'poster'       => 'locandina5.png',
                'tickets_url'  => 'www.javaancora.it/tickets',
                'cfp_url'      => 'www.javaancora.it/cfp',
            ],
            [
                'community_id' => 2, // ID per "Laravel Pordenone"
                'title'        => 'Laravel 10: Nuove Funzionalità e Best Practices',
                'description'  => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices.',
                'status'       => EventStatus::Pending,
                'type'         => EventType::Online,
                'start_date'   => now()->addDays(35),
                'end_date'     => now()->addDays(35)->addHours(2),
                'website'      => 'www.laravelprodenone.it',
                'poster'       => 'locandina6.jpg',
                'tickets_url'  => 'www.laravelprodenone.it/tickets',
                'cfp_url'      => 'www.laravelprodenone.it/cfp',
            ],
            [
                'community_id' => 3, // ID per "Wordpress Meetup Firenze"
                'title'        => 'Introduzione a Kubernetes e Docker',
                'description'  => 'Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione.',
                'status'       => EventStatus::Active,
                'type'         => EventType::Hybrid,
                'start_date'   => now()->addDays(30),
                'end_date'     => now()->addDays(30)->addHours(5),
                'website'      => 'www.devopsnapoli.it',
                'poster'       => null,
                'tickets_url'  => 'www.devopsnapoli.it/tickets',
                'cfp_url'      => 'www.devopsnapoli.it/cfp',
            ],
            [
                'community_id' => 1, // ID per "Java Ancona"
                'title'        => 'Java 17: Novità e Migrazione',
                'description'  => 'Partecipa a questo incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni. Un incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni. Un incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni.',
                'status'       => EventStatus::Active,
                'type'         => EventType::InPerson,
                'start_date'   => now()->addDays(25),
                'end_date'     => now()->addDays(25)->addHours(3),
                'website'      => 'www.javaancora.it',
                'poster'       => 'locandina7.jpg',
                'tickets_url'  => 'www.javaancora.it/tickets',
                'cfp_url'      => 'www.javaancora.it/cfp',
            ],
            [
                'community_id' => 2, // ID per "Laravel Pordenone"
                'title'        => 'Laravel 10: Nuove Funzionalità e Best Practices e proviamo anche un titolo più lungo direi, ottimo così.',
                'description'  => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices. Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices. Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices.',
                'status'       => EventStatus::Active,
                'type'         => EventType::Online,
                'start_date'   => now()->addDays(40),
                'end_date'     => now()->addDays(40)->addHours(2),
                'website'      => 'www.laravelprodenone.it',
                'poster'       => 'locandina8.jpg',
                'tickets_url'  => 'www.laravelprodenone.it/tickets',
                'cfp_url'      => 'www.laravelprodenone.it/cfp',
            ],
            [
                'community_id' => 3, // ID per "Wordpress Meetup Firenze"
                'title'        => 'Introduzione a Kubernetes e Docker',
                'description'  => 'Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione. Altro testo per il seminario. Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione.',
                'status'       => EventStatus::Reject,
                'type'         => EventType::Hybrid,
                'start_date'   => now()->addDays(35),
                'end_date'     => now()->addDays(35)->addHours(5),
                'website'      => 'www.devopsnapoli.it',
                'poster'       => 'locandina9.jpg',
                'tickets_url'  => 'www.devopsnapoli.it/tickets',
                'cfp_url'      => 'www.devopsnapoli.it/cfp',
            ],
            [
                'community_id' => 1, // ID per "Java Ancona"
                'title'        => 'Java 17: Novità e Migrazione',
                'description'  => 'Partecipa a questo incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni.',
                'status'       => EventStatus::Terminate,
                'type'         => EventType::InPerson,
                'start_date'   => now()->addDays(30),
                'end_date'     => now()->addDays(30)->addHours(3),
                'website'      => 'www.javaancora.it',
                'poster'       => 'locandina10.jpg',
                'tickets_url'  => 'www.javaancora.it/tickets',
                'cfp_url'      => 'www.javaancora.it/cfp',
            ],
            [
                'community_id' => 2, // ID per "Laravel Pordenone"
                'title'        => 'Laravel 10: Nuove Funzionalità e Best Practices',
                'description'  => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices.',
                'status'       => EventStatus::Pending,
                'type'         => EventType::Online,
                'start_date'   => now()->addDays(45),
                'end_date'     => now()->addDays(45)->addHours(2),
                'website'      => 'www.laravelprodenone.it',
                'poster'       => 'locandina11.jpg',
                'tickets_url'  => 'www.laravelprodenone.it/tickets',
                'cfp_url'      => 'www.laravelprodenone.it/cfp',
            ],
        ];

        foreach ($events as $eventData) {
            $address = AddressBook::query()->inRandomOrder()->first(); // Seleziona un indirizzo casuale per l'evento

            // Crea l'evento solo se esiste un indirizzo
            if ($address) {
                Event::query()->create([
                    'community_id'    => $eventData['community_id'],
                    'title'           => $eventData['title'],
                    'status'          => $eventData['status'],
                    'description'     => $eventData['description'],
                    'type'            => $eventData['type'],
                    'address_book_id' => $address->id,
                    'start_date'      => $eventData['start_date'],
                    'end_date'        => $eventData['end_date'],
                    'website'         => $eventData['website'],
                    'poster'          => $eventData['poster']
                        ? $basePosterUrl.$eventData['poster']
                        : null,
                    'tickets_url' => $eventData['tickets_url'],
                    'cfp_url'     => $eventData['cfp_url'],
                ]);
            } else {
                echo 'No address found for the event: '.$eventData['title']."\n";
            }
        }
    }
}
