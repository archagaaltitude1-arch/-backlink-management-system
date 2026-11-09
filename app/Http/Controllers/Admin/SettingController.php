<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'user' => auth()->user()?->load('role'),
        ]);
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

        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->save();

        return redirect()->route('admin.settings')->with('success', 'Account settings saved.');
    }
}
