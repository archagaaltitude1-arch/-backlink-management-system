<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffAuthController extends Controller
{
    /**
     * Show the staff login page.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('seo.dashboard');
        }

        return view('seo.login');
    }

    /**
     * Handle staff authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Ensure a staff profile exists for the logged in user
            $user = Auth::user();
            if ($user && ! $user->staffProfile) {
                StaffProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'designation' => 'SEO Specialist',
                        'contact_number' => '+94 77 123 4567',
                    ]
                );
            }

            return redirect()->intended(route('seo.dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log out the authenticated staff member.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seo.login')->with('info', 'You have been signed out.');
    }

    /**
     * Display the staff profile view.
     */
    public function profile(): View
    {
        $user = Auth::user();

        // Sample fallback user if unauthenticated for local preview
        $profile = [
            'name' => $user->name ?? 'Raweesha Lakruwan',
            'email' => $user->email ?? 'raweesha.lakruwan@example.com',
            'designation' => $user?->staffProfile?->designation ?? 'Senior SEO Specialist & Verifier',
            'contact_number' => $user?->staffProfile?->contact_number ?? '+94 77 987 6543',
            'role' => 'SEO Operations Staff',
            'joined_at' => $user?->created_at?->format('M d, Y') ?? 'Nov 01, 2026',
        ];

        return view('seo.profile', compact('profile'));
    }

    /**
     * Update the staff profile details.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . ($user?->id ?? 0)],
            'designation' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
        ]);

        if ($user) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            StaffProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'designation' => $validated['designation'],
                    'contact_number' => $validated['contact_number'],
                ]
            );
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the staff password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if ($user && ! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        if ($user) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return back()->with('success', 'Password updated successfully.');
    }
}
