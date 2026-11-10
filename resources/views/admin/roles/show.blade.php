@extends('layouts.admin')

@section('title', $role->role_name)

@section('content')
    @php
        $assigned = $role->permissions ?? [];
        $statusStyles = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'inactive' => 'bg-canvas text-navy/70',
            'suspended' => 'bg-red-50 text-red-700',
        ];
    @endphp
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.roles') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Roles & Permissions</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $role->role_name }}</h1>
                <p class="mt-2 text-sm text-navy/70">{{ $role->users_count }} assigned {{ $role->users_count === 1 ? 'user' : 'users' }}.</p>
            </div>
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn-primary">Edit</a>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Assigned permissions</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($permissions->whereIn('permission_name', $assigned) as $permission)
                    <span class="rounded-full bg-canvas px-3 py-1 text-xs font-medium">{{ $permission->permission_name }}</span>
                @empty
                    <p class="text-sm text-navy/60">No permissions are assigned.</p>
                @endforelse
            </div>
        </section>
        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold">Users</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[640px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Name</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Email</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($role->users as $user)
                            <tr>
                                <td class="px-5 py-3.5 font-medium sm:px-6">
                                    @if ($role->role_name === 'Customer')
                                        <a href="{{ route('admin.customers.show', $user) }}" class="hover:text-brand">{{ $user->name }}</a>
                                    @elseif ($role->role_name !== 'Admin')
                                        <a href="{{ route('admin.staff.show', $user) }}" class="hover:text-brand">{{ $user->name }}</a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $user->email }}</td>
                                <td class="px-5 py-3.5 sm:px-6">
                                    <span class="badge {{ $statusStyles[$user->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($user->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No users have this role.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
