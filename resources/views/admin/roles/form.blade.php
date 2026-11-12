@extends('layouts.admin')

@section('title', $role->exists ? 'Edit role' : 'Add role')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <section>
            <a href="{{ route('admin.roles') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Roles & Permissions</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $role->exists ? 'Edit role' : 'Add role' }}</h1>
        </section>
        @include('admin.niches.partials.alerts')
        <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($role->exists)
                @method('PUT')
            @endif
            <div>
                <label for="role_name" class="text-sm font-medium">Role name</label>
                <input id="role_name" name="role_name" type="text" value="{{ old('role_name', $role->role_name) }}" required maxlength="100" @if($role->role_name === 'Admin') readonly @endif class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                @error('role_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <fieldset>
                <legend class="text-sm font-medium">Permissions</legend>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-2 rounded-xl bg-canvas px-3 py-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->permission_name }}" class="mt-1" @checked(in_array($permission->permission_name, old('permissions', $role->permissions ?? []), true))>
                            <span>
                                <span class="block font-medium">{{ $permission->permission_name }}</span>
                                @if ($permission->description)
                                    <span class="block text-xs text-navy/55">{{ $permission->description }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('permissions') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </fieldset>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $role->exists ? 'Save changes' : 'Add role' }}</button>
                <a href="{{ $role->exists ? route('admin.roles.show', $role) : route('admin.roles') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
