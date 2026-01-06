<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProcessLogo
{
    public function execute($sourceFile): string
    {
        $filename = Str::uuid().'.webp';

        // Elaborazione immagine (v3)
        $encoded = Image::read($sourceFile)
            ->scale(width: 150)
            ->toWebp(quality: 80);

        Storage::disk('logos')->put($filename, (string) $encoded);

        return $filename;
    }
}
