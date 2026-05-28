<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Transformers\UserResource;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user->load(['roles', 'profile']))
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request->validated(), true);

        return response()->json([
            'token' => $data['access_token'] ?? null,
            'user' => new UserResource($data['user']->load(['roles', 'profile']))
        ]);
    }

    public function showLoginForm()
    {
        return view('auth::login');
    }

    public function webLogin(LoginRequest $request)
    {
        try {
            $this->authService->login($request->validated());
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->hasRole(['Driver', 'Chauffeur'])) {
                return redirect()->intended(route('driver.dashboard'));
            }

            return redirect()->intended(route('admin.dashboard'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load(['roles', 'profile']));
    }
}
