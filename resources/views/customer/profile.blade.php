@extends('layouts.customer')

@section('title', 'Profile')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">My account</p>
            <h1 class="mt-2 text-3xl font-semibold">Account details</h1>
        </div>
        @if (session('success'))
            <div class="rounded-xl bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <section class="panel p-6">
            <h2 class="text-lg font-semibold">Personal information</h2>
            <form method="POST" action="{{ route('customer.profile.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-sm font-medium" for="name">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $customer->name) }}" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium" for="phone">Phone</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div class="flex items-end"><button class="btn-primary" type="submit">Save details</button></div>
            </form>
        </section>

        <section id="security-settings" class="panel p-6">
            <h2 class="text-lg font-semibold">Security settings</h2>
            <form method="POST" action="{{ route('customer.password.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium" for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium" for="password">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium" for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div><button class="btn-navy" type="submit">Update password</button></div>
            </form>
        </section>
    </div>
@endsection
