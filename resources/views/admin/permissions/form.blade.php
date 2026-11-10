@extends('layouts.admin')

@section('title', $permission->exists ? 'Edit permission' : 'Add permission')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <a href="{{ route('admin.roles') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Roles & Permissions</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $permission->exists ? 'Edit permission' : 'Add permission' }}</h1>
        </section>
        <form method="POST" action="{{ $permission->exists ? route('admin.permissions.update', $permission) : route('admin.permissions.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($permission->exists)
                @method('PUT')
            @endif
            <div>
                <label for="permission_name" class="text-sm font-medium">Permission name</label>
                <input id="permission_name" name="permission_name" type="text" value="{{ old('permission_name', $permission->permission_name) }}" required maxlength="150" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                @error('permission_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description" class="text-sm font-medium">Description</label>
                <input id="description" name="description" type="text" value="{{ old('description', $permission->description) }}" maxlength="255" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $permission->exists ? 'Save changes' : 'Add permission' }}</button>
                <a href="{{ route('admin.roles') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
