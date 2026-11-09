@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    @php $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30'; @endphp
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">Settings</h1>
            <p class="mt-2 text-sm leading-6 text-navy/70">Update the signed-in admin account. Role stays as assigned.</p>
        </section>
        @include('admin.niches.partials.alerts')
        <form method="POST" action="{{ route('admin.settings.update') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="text-sm font-medium">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" class="{{ $input }}">
                @error('name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="{{ $input }}">
                @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <p class="text-sm font-medium">Role</p>
                <p class="mt-2 rounded-xl bg-canvas px-4 py-3 text-sm">{{ $user->role?->role_name ?? '—' }}</p>
            </div>
            <div>
                <label for="current_password" class="text-sm font-medium">Current password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="{{ $input }}">
                @error('current_password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="text-sm font-medium">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="{{ $input }}">
                    @error('password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="text-sm font-medium">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $input }}">
                </div>
            </div>
            <p class="text-sm text-navy/60">Leave the password fields blank to keep the current password.</p>
            <button type="submit" class="btn-primary">Save account</button>
        </form>
    </div>
@endsection
