@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    @php $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30'; @endphp
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">Admin profile</h1>
            <p class="mt-2 text-sm leading-6 text-navy/70">Update your own name, email, photo, and password. Role stays as assigned.</p>
        </section>
        @include('admin.niches.partials.alerts')
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-4">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="" class="h-16 w-16 rounded-full object-cover">
                @else
                    <span class="grid h-16 w-16 place-items-center rounded-full bg-navy text-lg font-semibold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @endif
                <div class="min-w-0 flex-1">
                    <label for="photo" class="text-sm font-medium">Profile picture</label>
                    <input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm">
                    @error('photo') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
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
