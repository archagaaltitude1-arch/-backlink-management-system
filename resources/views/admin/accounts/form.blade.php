@extends('layouts.admin')

@section('title', $account->exists ? 'Edit account' : 'Add account')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <a href="{{ route($routeBase) }}" class="text-xs font-semibold tracking-wider text-brand uppercase">{{ $heading }}</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $account->exists ? 'Edit account' : 'Add account' }}</h1>
            <p class="mt-2 text-sm leading-6 text-navy/70">{{ $audience === 'staff' ? 'Set the staff member, role, and account status.' : 'Set the customer name, email, and account status.' }}</p>
        </section>

        @include('admin.niches.partials.alerts')

        <form method="POST" action="{{ $account->exists ? route($routeBase.'.update', $account) : route($routeBase.'.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($account->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="text-sm font-medium">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $account->name) }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                @error('name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $account->email) }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" @unless($account->exists) required @endunless autocomplete="new-password" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30" placeholder="{{ $account->exists ? 'Leave blank to keep the current password' : '' }}">
                @error('password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            @if ($audience === 'staff')
                <div>
                    <label for="role_id" class="text-sm font-medium">Role</label>
                    <select id="role_id" name="role_id" required class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                        <option value="">Select a role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id', $account->role_id) === (string) $role->id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            @endif
            <div>
                <label for="status" class="text-sm font-medium">Status</label>
                <select id="status" name="status" required class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                    @foreach (['active', 'inactive', 'suspended'] as $option)
                        <option value="{{ $option }}" @selected(old('status', $account->status ?: 'active') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $account->exists ? 'Save changes' : 'Add account' }}</button>
                <a href="{{ $account->exists ? route($routeBase.'.show', $account) : route($routeBase) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
