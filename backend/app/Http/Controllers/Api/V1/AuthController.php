<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Permissions\V1\Abilities;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Login
     *
     * Authenticates the user and returns the token.
     *
     * @unauthenticated
     *
     * @group Authentication
     *
     * @response 200 {
            "data": {
                "token": "{YOUR_AUTH_KEY}"
            },
            "message": "Authenticated",
            "status": 200
        }
     */
    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Credenziali non valide', 401);
        }

        $user = User::firstWhere('email', $request->email);

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken('Api token for '.$user->email, Abilities::getAbilities($user), now()->addHours(4))->plainTextToken,
                'user' => new UserResource($user),
            ],
        );
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|max:255|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return $this->ok(
            'Registered',
            [
                'token' => $user->createToken('Api token for '.$user->email, Abilities::getAbilities($user), now()->addHours(4))->plainTextToken,
                'user' => new UserResource($user),
            ],
        );
    }

    /**
     * Logout
     *
     * Signs out.
     *
     * @group Authentication
     *
     * @response 200 {}
     */
    public function logout(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }
}
