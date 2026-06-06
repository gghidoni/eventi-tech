<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProcessPoster
{
    /**
     * @return array{desktop:string, mobile:string, thumb:string}
     */
    public function execute(UploadedFile $sourceFile): array
    {
        $filename = Str::uuid().'.webp';
        $path = $sourceFile->getRealPath();

        // 1. VERSIONE DESKTOP (1000px)
        $desktop = Image::decode($path)
            ->scale(width: 1200)
            ->encode(new WebpEncoder(quality: 90));
        Storage::disk('posters')->put($filename, (string) $desktop);

        // 2. VERSIONE MOBILE (500px)
        $mobile = Image::decode($path)
            ->scale(width: 500)
            ->encode(new WebpEncoder(quality: 90));
        Storage::disk('posters')->put('mobile/'.$filename, (string) $mobile);

        // 3. VERSIONE THUMBNAIL (150px)
        $thumb = Image::decode($path)
            ->scale(height: 150)
            ->encode(new WebpEncoder(quality: 90));
        Storage::disk('posters')->put('thumbs/'.$filename, (string) $thumb);

        return [
            'desktop' => $filename,
            'mobile'  => 'mobile/'.$filename,
            'thumb'   => 'thumbs/'.$filename,
        ];
    }
}
