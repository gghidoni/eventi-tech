<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\EventResource;
use App\Policies\V1\UserPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends ApiController
{
    protected $policyClass = UserPolicy::class;

    public function getBookmarks(Request $request)
    {
        $user = $request->user();

        try {

            Gate::authorize('showBookmarks', $user);
            $bookmarks = EventResource::collection($user->bookmarks);

            return $this->success('Operazione completata', $bookmarks);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->error('Forbidden: '.$e->getMessage(), 403);
        }
    }
}
