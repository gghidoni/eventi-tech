<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProcessLogo
{
    public function execute(UploadedFile $sourceFile): string
    {
        $filename = Str::uuid().'.webp';

        // Elaborazione immagine (v3)
        $encoded = Image::decode($sourceFile)
            ->scale(width: 150)
            ->encode(new WebpEncoder(quality: 80));

        Storage::disk('logos')->put($filename, (string) $encoded);

        return $filename;
    }
}
