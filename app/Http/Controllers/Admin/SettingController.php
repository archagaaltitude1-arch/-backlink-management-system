<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingController extends Controller
{
    public function edit(AdminWorkspace $workspace): View
    {
        $user = auth()->user()?->load('role');

        return view('admin.settings.edit', [
            'user' => $user,
            'photoUrl' => $user ? ($workspace->photoPath($user) ? route('admin.profile-photo') : null) : null,
        ]);
    }

    public function photo(AdminWorkspace $workspace): BinaryFileResponse
    {
        $user = auth()->user();
        $path = $user ? $workspace->photoPath($user) : null;
        abort_unless($path !== null, 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($request->filled('password')) {
            if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
                return back()->withInput()->withErrors([
                    'current_password' => 'The current password is incorrect.',
                ]);
            }

            $user->password = $request->validated('password');
        }

        if ($request->hasFile('photo')) {
            app(AdminWorkspace::class)->storePhoto($user, $request->file('photo'));
        }

        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->save();

        return redirect()->route('admin.settings')->with('success', 'Account settings saved.');
    }
}
