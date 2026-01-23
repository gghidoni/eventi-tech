<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProcessAvatar
{
    public function execute($sourceFile): string
    {
        $filename = Str::uuid().'.webp';

        // Crop quadrato e resize
        $encoded = Image::read($sourceFile)
            ->cover(200, 200)
            ->toWebp(quality: 80);

        Storage::disk('public')->put('avatars/'.$filename, (string) $encoded);

        return 'avatars/'.$filename;
    }
}
