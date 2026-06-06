<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProcessAvatar
{
    public function execute(UploadedFile $sourceFile): string
    {
        $filename = Str::uuid().'.webp';

        // Crop quadrato e resize
        $encoded = Image::decode($sourceFile)
            ->cover(200, 200)
            ->encode(new WebpEncoder(quality: 80));

        Storage::disk('public')->put('avatars/'.$filename, (string) $encoded);

        return 'avatars/'.$filename;
    }
}
