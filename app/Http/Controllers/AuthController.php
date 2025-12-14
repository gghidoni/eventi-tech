<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // use ApiResponses;

    public function login(LoginUserRequest $request): RedirectResponse
    {
        $request->validated($request->all());

        if (! Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function register(Request $request)
    {
        // $request->validate([
        //     'name'     => 'required|string|max:50',
        //     'email'    => 'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|max:255|min:8',
        // ]);

        // $user = User::create([
        //     'name'     => $request->name,
        //     'email'    => $request->email,
        //     'password' => bcrypt($request->password),
        // ]);

        // return $this->ok(
        //     'Registered',
        //     [
        //         'token' => $user->createToken('Api token for '.$user->email, Abilities::getAbilities($user), now()->addHours(4))->plainTextToken,
        //         'user'  => new UserResource($user),
        //     ],
        // );
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
