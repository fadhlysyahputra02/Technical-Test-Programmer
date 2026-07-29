<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and assign the 'applicant' role.
     *
     * POST /api/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('applicant');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data'    => new UserResource($user->loadMissing('roles', 'permissions')),
            'token'   => $token,
        ], 201);
    }

    /**
     * Authenticate the user and return a Sanctum token.
     *
     * POST /api/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data'    => new UserResource($user->loadMissing('roles', 'permissions')),
            'token'   => $token,
        ]);
    }

    /**
     * Revoke the current user's token.
     *
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Return the currently authenticated user.
     *
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles', 'permissions');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
