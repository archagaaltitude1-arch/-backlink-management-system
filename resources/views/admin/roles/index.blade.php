@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Roles & Permissions</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Roles and the permissions assigned to them.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.permissions.create') }}" class="rounded-xl bg-canvas px-4 py-2.5 text-sm font-medium hover:bg-brand/10">Add permission</a>
                <a href="{{ route('admin.roles.create') }}" class="btn-primary">Add role</a>
            </div>
        </section>

        @include('admin.niches.partials.alerts')

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Roles</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $roleCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Permissions</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $permissionCount }}</p>
            </article>
        </section>

        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.roles') }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search roles</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by role name" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-[720px] w-full text-left text-sm">
                    <caption class="sr-only">Roles</caption>
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Role</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Users</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Permissions</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($roles as $role)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium sm:px-6">
                                    <a href="{{ route('admin.roles.show', $role) }}" class="hover:text-brand">{{ $role->role_name }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $role->users_count }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ count($role->permissions ?? []) }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.roles.show', $role) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="ml-3 font-semibold text-brand hover:text-brand-dark">Edit</a>
                                    @if ($role->role_name !== 'Admin' && $role->users_count === 0)
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline" onsubmit="return confirm(@js('Delete '.$role->role_name.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No roles match this search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $roles])
        </section>

        <section class="panel overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold">Permissions</h2>
                    <p class="mt-1 text-sm text-navy/60">Permission names that can be assigned to a role.</p>
                </div>
                <a href="{{ route('admin.permissions.create') }}" class="text-sm font-semibold text-brand hover:text-brand-dark">Add permission</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[680px] w-full text-left text-sm">
                    <caption class="sr-only">Permissions</caption>
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Permission</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Description</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($permissions as $permission)
                            <tr>
                                <td class="px-5 py-3.5 font-medium sm:px-6">{{ $permission->permission_name }}</td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $permission->description ?: '—' }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="font-semibold text-brand hover:text-brand-dark">Edit</a>
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" class="inline" onsubmit="return confirm(@js('Delete '.$permission->permission_name.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No permissions have been added.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
