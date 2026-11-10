<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->authenticateAndAdoptCart($request, $user);

        if ($request->expectsJson()) {
            return response()->json(['data' => $user->only(['id', 'name', 'email', 'role'])], 201);
        }

        return redirect()->route('customer.dashboard');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = mb_strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return back()->withErrors(['email' => 'Too many login attempts. Please try again later.'])->onlyInput('email');
        }

        if (! Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'status' => 'active',
        ], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $this->adoptCart($request, $request->user());

        if ($request->expectsJson()) {
            return response()->json(['data' => $request->user()->only(['id', 'name', 'email', 'role'])]);
        }

        return redirect()->intended(route('customer.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    private function authenticateAndAdoptCart(Request $request, User $user): void
    {
        $previousSessionId = $request->session()->getId();
        Auth::login($user);
        $request->session()->regenerate();
        $this->moveCart($previousSessionId, $request->session()->getId(), $user);
    }

    private function adoptCart(Request $request, User $user): void
    {
        $previousSessionId = $request->session()->getId();
        $request->session()->regenerate();
        $this->moveCart($previousSessionId, $request->session()->getId(), $user);
    }

    private function moveCart(string $previousSessionId, string $sessionId, User $user): void
    {
        Cart::where('session_id', $previousSessionId)->update([
            'session_id' => $sessionId,
            'customer_id' => $user->id,
        ]);
    }
}
