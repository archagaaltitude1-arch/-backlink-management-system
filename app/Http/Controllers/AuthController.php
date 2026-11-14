<?php

namespace App\Http\Controllers;

use App\Http\FirebaseGoogleToken;
use App\Models\Cart;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
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
            'account_type' => 'customer',
            'status' => 'active',
        ]);

        $this->authenticateAndAdoptCart($request, $user);

        if ($request->expectsJson()) {
            return response()->json(['data' => $user->only(['id', 'name', 'email']) + ['role' => 'customer']], 201);
        }

        return redirect()->route('customer.dashboard');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return $this->googleLoginError('Google sign-in is not configured.');
        }

        $state = Str::random(64);
        $verifier = Str::random(64);
        $request->session()->put('google_oauth', [
            'state' => $state, 'verifier' => $verifier, 'expires' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->googleRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $pending = $request->session()->pull('google_oauth');
        $state = $request->query('state');
        $code = $request->query('code');

        if (! is_array($pending) || now()->timestamp > $pending['expires'] || ! is_string($state)
            || ! hash_equals($pending['state'], $state) || ! is_string($code) || $code === '') {
            return $this->googleLoginError('Google sign-in expired or was cancelled. Please try again.');
        }

        try {
            $token = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->googleRedirectUri(),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'code_verifier' => $pending['verifier'],
            ]);
            $accessToken = $token->json('access_token');

            if (! $token->successful() || ! is_string($accessToken) || $accessToken === '') {
                return $this->googleLoginError('Google sign-in could not be completed. Please try again.');
            }

            $profile = Http::withToken($accessToken)->timeout(10)->get('https://openidconnect.googleapis.com/v1/userinfo');
        } catch (ConnectionException) {
            return $this->googleLoginError('Google sign-in is temporarily unavailable. Please try again.');
        }

        $subject = $profile->json('sub');
        $email = $profile->json('email');
        if (! $profile->successful() || $profile->json('email_verified') !== true || ! is_string($subject)
            || $subject === '' || strlen($subject) > 255 || ! is_string($email) || strlen($email) > 255
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->googleLoginError('Use a Google account with a verified email address.');
        }

        return $this->signInGoogleCustomer($request, $subject, $email, $profile->json('name'));
    }

    public function loginWithFirebase(Request $request, FirebaseGoogleToken $firebase): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['id_token' => ['required', 'string', 'max:8192']]);

        try {
            $profile = $firebase->verify($data['id_token']);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Google sign-in is temporarily unavailable. Please try again.'], 503);
        }

        if ($profile === null) {
            throw ValidationException::withMessages(['google' => 'Google sign-in could not be verified. Please try again.']);
        }

        return $this->signInGoogleCustomer($request, $profile['subject'], $profile['email'], $profile['name']);
    }

    private function signInGoogleCustomer(Request $request, string $subject, string $email, mixed $name): RedirectResponse|JsonResponse
    {
        $email = Str::lower($email);
        $user = User::query()->where('google_subject', $subject)->first();
        if ($user === null) {
            if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                return $this->googleLoginError('An account already uses this email. Sign in with your password.');
            }

            $user = User::query()->createOrFirst(['google_subject' => $subject], [
                'name' => is_string($name) && trim($name) !== '' ? Str::limit($name, 255, '') : $email,
                'email' => $email,
                'password' => Str::random(64),
                'account_type' => 'customer',
                'status' => 'active',
            ]);
        }

        if ($user->status !== 'active' || $user->role?->role_name !== 'Customer') {
            return $this->googleLoginError('This account cannot sign in as a customer.');
        }

        $this->authenticateAndAdoptCart($request, $user);

        if ($request->routeIs('auth.google.firebase')) {
            return response()->json(['redirect' => $request->session()->pull('url.intended', route('customer.dashboard'))]);
        }

        return redirect()->intended(route('customer.dashboard'));
    }

    private function googleRedirectUri(): string
    {
        return config('services.google.redirect_uri') ?: route('auth.google.callback');
    }

    private function googleLoginError(string $message): RedirectResponse
    {
        if (request()->routeIs('auth.google.firebase')) {
            throw ValidationException::withMessages(['google' => $message]);
        }

        return redirect()->route('auth.login')->withErrors(['google' => $message]);
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

        $previousSessionId = $request->session()->getId();

        if (! Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []])->id,
            'status' => 'active',
        ], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $this->moveCart($previousSessionId, $request->session()->getId(), $request->user());

        if ($request->expectsJson()) {
            return response()->json(['data' => $request->user()->only(['id', 'name', 'email']) + ['role' => 'customer']]);
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

    private function moveCart(string $previousSessionId, string $sessionId, User $user): void
    {
        Cart::where('session_id', $previousSessionId)->update([
            'session_id' => $sessionId,
            'customer_id' => $user->id,
        ]);
    }
}
