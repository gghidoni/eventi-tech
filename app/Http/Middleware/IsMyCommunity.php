<?php

namespace App\Http\Middleware;

use App\Models\Community;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsMyCommunity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $community = $request->route('community');

        // Blocchiamo se manca l'utente o se il route model binding non risolve una Community.
        if (!$user || !$community instanceof Community) {
            abort(403);
        }

        if ($user->communities()->whereKey($community->id)->doesntExist()) {
            abort(403);
        }

        return $next($request);
    }
}
