<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type'       => 'user',
            'id'         => $this->id,
            'attributes' => [
                'name'      => $this->name,
                'email'     => $this->email,
                'is_admin'  => $this->is_admin,
                'avatar'    => $this->avatar_img,
                'website'   => $this->website,
                'linkedin'  => $this->linkedin,
                'instagram' => $this->instagram,
                'facebook'  => $this->facebook,
            ],
            'relationships' => [
                'bookmarks' => $this->bookmarks->pluck('id'),
            ],
        ];
    }
}
