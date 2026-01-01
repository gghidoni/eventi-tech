<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\AddressBook\AddressBook;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

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
                'community_id'        => 1, // ID per "Java Ancona"
                'title'               => 'Java & Spring Boot Workshop',
                'description'         => 'Un workshop pratico per sviluppatori Java. Impara a creare applicazioni scalabili con Spring Boot.',
                'status'              => EventStatus::Active,
                'type'                => EventType::InPerson,
                'start_date'          => now()->addDays(5),
                'end_date'            => now()->addDays(5)->addHours(4),
                'website'             => 'www.javaancora.it',
                'poster_source'       => 'locandina1.png',
                'tickets_url'         => 'www.javaancora.it/tickets',
                'cfp_url'             => 'www.javaancora.it/cfp',
            ],
            [
                'community_id'        => 2, // ID per "Laravel Pordenone"
                'title'               => 'Laravel 10: Nuove Funzionalità e Best Practices, titolo lungo per vedere se si tronca il titolo',
                'description'         => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices. Impara a creare applicazioni scalabili con Laravel.',
                'status'              => EventStatus::Pending,
                'type'                => EventType::Online,
                'start_date'          => now()->addDays(10),
                'end_date'            => now()->addDays(10)->addHours(2),
                'website'             => 'www.laravelprodenone.it',
                'poster_source'       => 'locandina15.jpg',
                'tickets_url'         => 'www.laravelprodenone.it/tickets',
                'cfp_url'             => 'www.laravelprodenone.it/cfp',
            ],
            [
                'community_id'        => 3, // ID per "Wordpress Meetup Firenze"
                'title'               => 'Introduzione a Gutenberg e Blocchi Personalizzati, titolo lungo per vedere se si tronca',
                'description'         => 'Un evento per esplorare la creazione di blocchi personalizzati in Gutenberg per WordPress. Impara a creare contenuti personalizzati con Gutenberg.',
                'status'              => EventStatus::Active,
                'type'                => EventType::Hybrid,
                'start_date'          => now()->addDays(20),
                'end_date'            => now()->addDays(20)->addHours(3),
                'website'             => 'www.wordpressfirenze.it',
                'poster_source'       => 'locandina12.jpg',
                'tickets_url'         => 'www.wordpressfirenze.it/tickets',
                'cfp_url'             => 'www.wordpressfirenze.it/cfp',
            ],
            [
                'community_id'        => 1, // ID per "Java Ancona"
                'title'               => 'PHP 8.1: Novità e Migrazione',
                'description'         => 'Partecipa a questo incontro per scoprire le novità introdotte in PHP 8.1 e come migrare le tue applicazioni.',
                'status'              => EventStatus::Reject,
                'type'                => EventType::InPerson,
                'start_date'          => now()->addDays(15),
                'end_date'            => now()->addDays(15)->addHours(3),
                'website'             => 'www.phpverona.it',
                'poster_source'       => 'locandina3.jpg',
                'tickets_url'         => 'www.phpverona.it/tickets',
                'cfp_url'             => 'www.phpverona.it/cfp',
            ],
            [
                'community_id'        => 2, // ID per "Laravel Pordenone"
                'title'               => 'State Management in React con Redux',
                'description'         => 'Un workshop intensivo su come utilizzare Redux per una gestione avanzata dello stato nelle app React.',
                'status'              => EventStatus::Active,
                'type'                => EventType::Online,
                'start_date'          => now()->addDays(30),
                'end_date'            => now()->addDays(30)->addHours(3),
                'website'             => 'www.reactroma.it',
                'poster_source'       => 'locandina4.webp',
                'tickets_url'         => 'www.reactroma.it/tickets',
                'cfp_url'             => 'www.reactroma.it/cfp',
            ],
            [
                'community_id'        => 3, // ID per "Wordpress Meetup Firenze"
                'title'               => 'Introduzione a Kubernetes e Docker',
                'description'         => 'Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione.',
                'status'              => EventStatus::Terminate,
                'type'                => EventType::Hybrid,
                'start_date'          => now()->addDays(25),
                'end_date'            => now()->addDays(25)->addHours(5),
                'website'             => 'www.devopsnapoli.it',
                'poster_source'       => null,
                'tickets_url'         => 'www.devopsnapoli.it/tickets',
                'cfp_url'             => 'www.devopsnapoli.it/cfp',
            ],
            [
                'community_id'        => 1, // ID per "Java Ancona"
                'title'               => 'Java 17: Novità e Migrazione',
                'description'         => 'Partecipa a questo incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni.',
                'status'              => EventStatus::Active,
                'type'                => EventType::InPerson,
                'start_date'          => now()->addDays(20),
                'end_date'            => now()->addDays(20)->addHours(3),
                'website'             => 'www.javaancora.it',
                'poster_source'       => 'locandina5.png',
                'tickets_url'         => 'www.javaancora.it/tickets',
                'cfp_url'             => 'www.javaancora.it/cfp',
            ],
            [
                'community_id'        => 2, // ID per "Laravel Pordenone"
                'title'               => 'Laravel 10: Nuove Funzionalità e Best Practices',
                'description'         => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices.',
                'status'              => EventStatus::Pending,
                'type'                => EventType::Online,
                'start_date'          => now()->addDays(35),
                'end_date'            => now()->addDays(35)->addHours(2),
                'website'             => 'www.laravelprodenone.it',
                'poster_source'       => 'locandina6.jpg',
                'tickets_url'         => 'www.laravelprodenone.it/tickets',
                'cfp_url'             => 'www.laravelprodenone.it/cfp',
            ],
            [
                'community_id'        => 3, // ID per "Wordpress Meetup Firenze"
                'title'               => 'Introduzione a Kubernetes e Docker',
                'description'         => 'Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione.',
                'status'              => EventStatus::Active,
                'type'                => EventType::Hybrid,
                'start_date'          => now()->addDays(30),
                'end_date'            => now()->addDays(30)->addHours(5),
                'website'             => 'www.devopsnapoli.it',
                'poster_source'       => null,
                'tickets_url'         => 'www.devopsnapoli.it/tickets',
                'cfp_url'             => 'www.devopsnapoli.it/cfp',
            ],
            [
                'community_id'        => 1, // ID per "Java Ancona"
                'title'               => 'Java 17: Novità e Migrazione',
                'description'         => 'Partecipa a questo incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni. Un incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni. Un incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni.',
                'status'              => EventStatus::Active,
                'type'                => EventType::InPerson,
                'start_date'          => now()->addDays(25),
                'end_date'            => now()->addDays(25)->addHours(3),
                'website'             => 'www.javaancora.it',
                'poster_source'       => 'locandina7.jpg',
                'tickets_url'         => 'www.javaancora.it/tickets',
                'cfp_url'             => 'www.javaancora.it/cfp',
            ],
            [
                'community_id'        => 2, // ID per "Laravel Pordenone"
                'title'               => 'Laravel 10: Nuove Funzionalità e Best Practices e proviamo anche un titolo più lungo direi, ottimo così.',
                'description'         => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices. Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices. Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices.',
                'status'              => EventStatus::Active,
                'type'                => EventType::Online,
                'start_date'          => now()->addDays(40),
                'end_date'            => now()->addDays(40)->addHours(2),
                'website'             => 'www.laravelprodenone.it',
                'poster_source'       => 'locandina8.jpg',
                'tickets_url'         => 'www.laravelprodenone.it/tickets',
                'cfp_url'             => 'www.laravelprodenone.it/cfp',
            ],
            [
                'community_id'        => 3, // ID per "Wordpress Meetup Firenze"
                'title'               => 'Introduzione a Kubernetes e Docker',
                'description'         => 'Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione. Altro testo per il seminario. Un seminario per imparare a utilizzare Docker e Kubernetes per la gestione dei container in produzione.',
                'status'              => EventStatus::Reject,
                'type'                => EventType::Hybrid,
                'start_date'          => now()->addDays(35),
                'end_date'            => now()->addDays(35)->addHours(5),
                'website'             => 'www.devopsnapoli.it',
                'poster_source'       => 'locandina9.jpg',
                'tickets_url'         => 'www.devopsnapoli.it/tickets',
                'cfp_url'             => 'www.devopsnapoli.it/cfp',
            ],
            [
                'community_id'        => 1, // ID per "Java Ancona"
                'title'               => 'Java 17: Novità e Migrazione',
                'description'         => 'Partecipa a questo incontro per scoprire le novità introdotte in Java 17 e come migrare le tue applicazioni.',
                'status'              => EventStatus::Terminate,
                'type'                => EventType::InPerson,
                'start_date'          => now()->addDays(30),
                'end_date'            => now()->addDays(30)->addHours(3),
                'website'             => 'www.javaancora.it',
                'poster_source'       => 'locandina10.jpg',
                'tickets_url'         => 'www.javaancora.it/tickets',
                'cfp_url'             => 'www.javaancora.it/cfp',
            ],
            [
                'community_id'        => 2, // ID per "Laravel Pordenone"
                'title'               => 'Laravel 10: Nuove Funzionalità e Best Practices',
                'description'         => 'Un incontro per esplorare le nuove funzionalità di Laravel 10 e condividere best practices.',
                'status'              => EventStatus::Pending,
                'type'                => EventType::Online,
                'start_date'          => now()->addDays(45),
                'end_date'            => now()->addDays(45)->addHours(2),
                'website'             => 'www.laravelprodenone.it',
                'poster_source'       => 'locandina11.jpg',
                'tickets_url'         => 'www.laravelprodenone.it/tickets',
                'cfp_url'             => 'www.laravelprodenone.it/cfp',
            ],
        ];

        foreach ($events as $eventData) {
            $address = AddressBook::query()->inRandomOrder()->first(); // Seleziona un indirizzo casuale per l'evento

            // Crea l'evento solo se esiste un indirizzo
            if ($address) {

                $paths = $this->processPoster($eventData['poster_source'] ?? null);

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
                    'poster'          => $paths['desktop'] ?? null,
                    'poster_mobile'   => $paths['mobile'] ?? null,
                    'poster_thumb'    => $paths['thumb'] ?? null,
                    'tickets_url'     => $eventData['tickets_url'],
                    'cfp_url'         => $eventData['cfp_url'],
                ]);
            } else {
                echo 'No address found for the event: '.$eventData['title']."\n";
            }
        }
    }

    private function processPoster(?string $sourceFile): array
    {
        if (!$sourceFile) {
            return [];
        }

        // Percorso dove hai messo le immagini per il seed (es: database/seeders/images/locandina1.png)
        $sourcePath = database_path('seeders/images/'.$sourceFile);

        if (!File::exists($sourcePath)) {
            echo "File non trovato: $sourcePath \n";

            return [];
        }

        $filename = Str::uuid().'.webp';

        // 1. VERSIONE DESKTOP (1000px è perfetta, bilancia bene qualità e peso)
        $desktop = Image::read($sourcePath)
            ->scale(width: 1200)
            ->toWebp(quality: 80);
        Storage::disk('posters')->put($filename, (string) $desktop);

        // 2. VERSIONE MOBILE (400px)
        $mobile = Image::read($sourcePath)
            ->scale(width: 400)
            ->toWebp(quality: 80);
        Storage::disk('posters')->put('mobile/'.$filename, (string) $mobile);

        // 3. VERSIONE THUMBNAIL (150px)
        $thumb = Image::read($sourcePath)
            ->scale(height: 120)
            ->toWebp(quality: 80);
        Storage::disk('posters')->put('thumbs/'.$filename, (string) $thumb);

        return [
            'desktop' => $filename,
            'mobile'  => 'mobile/'.$filename,
            'thumb'   => 'thumbs/'.$filename,
        ];
    }
}
