<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PlaceholderSeeder extends Seeder
{
    public function run(): void
    {
        // Percorso dove si trovano i 10 PNG originali
        $sourcePath = database_path('seeders/images/placeholders/');

        if (!File::exists($sourcePath)) {
            $this->command->error("Cartella sorgente non trovata: $sourcePath");

            return;
        }

        $this->command->info('Inizio ottimizzazione placeholder...');

        for ($i = 1; $i <= 10; $i++) {
            $sourceFile = $sourcePath."placeholder-{$i}.png";

            if (!File::exists($sourceFile)) {
                $this->command->warn("File placeholder-{$i}.png non trovato, salto...");

                continue;
            }

            // Definiamo i tagli necessari
            $sizes = [
                ['folder' => '',        'width' => 1200, 'quality' => 90], // Desktop
                ['folder' => 'mobile/', 'width' => 500,  'quality' => 90], // Mobile
                ['folder' => 'thumbs/', 'width' => 300,  'quality' => 90], // Thumb
            ];

            foreach ($sizes as $size) {
                $filename = "placeholder-{$i}.webp";
                $targetPath = $size['folder'].$filename;

                // Elaborazione con Intervention Image v3
                $encoded = Image::read($sourceFile)
                    ->scale(width: $size['width'])
                    ->toWebp(quality: $size['quality']);

                Storage::disk('posters')->put($targetPath, (string) $encoded);
            }

            $this->command->comment("Placeholder {$i} elaborato nei 3 formati.");
        }

        $this->command->info("Tutti i placeholder sono stati ottimizzati nel disco 'posters'!");
    }
}
