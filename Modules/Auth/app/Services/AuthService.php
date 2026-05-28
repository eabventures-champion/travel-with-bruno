<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'user_type' => $data['user_type'] ?? 'customer',
            'status' => 'active',
        ]);

        // Assign default role
        $user->assignRole($user->user_type);

        // Create profile
        $user->profile()->create();

        return $user;
    }

    public function login(array $credentials, bool $generateToken = false)
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        $data = [
            'user' => $user,
        ];

        if ($generateToken) {
            $token = $user->createToken('auth_token')->plainTextToken;
            $data['access_token'] = $token;
            $data['token_type'] = 'Bearer';
        }

        return $data;
    }

    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();
    }
}
