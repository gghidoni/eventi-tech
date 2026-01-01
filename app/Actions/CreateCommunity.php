<?php

namespace App\Actions;

use App\Enums\CommunityStatus;
use App\Mail\CreatedNewCommunity;
use App\Models\Community;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class CreateCommunity
{
    public function execute(array $data, $logoFile = null): Community
    {
        if ($logoFile) {
            $filename = Str::uuid().'.webp';

            // Elaborazione immagine (v3)
            $encoded = Image::read($logoFile)
                ->scale(width: 150)
                ->toWebp(quality: 80);

            Storage::disk('logos')->put($filename, (string) $encoded);

            $data['logo'] = $filename;
        }

        $data['status'] = CommunityStatus::Pending->value;
        $data['slug'] = str()->slug($data['name']);

        $user = auth()->user();
        $community = $user->communities()->create($data);
        Mail::to($user)->send(new CreatedNewCommunity($community));

        return $community;
    }
}
