<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class EventTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Otteniamo tutti gli eventi dal database
        $events = Event::all();

        // Otteniamo tutti i tag dal database
        $tags = Tag::all();

        // Cicliamo sugli eventi per assegnare loro dei tag casuali
        foreach ($events as $event) {
            // Selezioniamo un numero casuale di tag (ad esempio tra 2 e 5) da associare all'evento
            $randomTags = $tags->random(mt_rand(2, 5)); // Utilizza rand per il numero di tag da selezionare

            // Associare i tag selezionati all'evento
            $event->tags()->attach($randomTags);
        }
    }
}
