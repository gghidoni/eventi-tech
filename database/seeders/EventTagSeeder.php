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
        // Otteniamo tutti gli eventi dal database.
        $events = Event::all();

        // Otteniamo tutti i tag dal database.
        $tags = Tag::all();

        // Cicliamo sugli eventi per assegnare loro da 0 a 4 tag casuali.
        foreach ($events as $event) {
            // Manteniamo sempre il limite massimo a 4, ma non superiamo i tag realmente disponibili.
            $tagsCount = mt_rand(0, min(4, $tags->count()));

            if ($tagsCount === 0) {
                continue;
            }

            // Selezioniamo i tag random e li associamo all'evento.
            $randomTagIds = $tags->random($tagsCount)->pluck('id')->all();
            $event->tags()->attach($randomTagIds);
        }
    }
}
