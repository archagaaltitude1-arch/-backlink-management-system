@extends('layouts.admin')

@section('title', $heading)

@section('content')
    @php
        $statusStyles = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'inactive' => 'bg-canvas text-navy/70',
            'suspended' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">{{ $heading }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">{{ $summary }}</p>
            </div>
            <a href="{{ route($routeBase.'.create') }}" class="btn-primary">Add {{ $singular }}</a>
        </section>

        @include('admin.niches.partials.alerts')

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Total</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Active</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums text-emerald-700">{{ $activeCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Inactive</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $inactiveCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Suspended</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums text-red-700">{{ $suspendedCount }}</p>
            </article>
        </section>

        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route($routeBase) }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by name or email" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="status" class="text-sm font-medium">Status</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 sm:w-44">
                        <option value="" @selected($status === '')>All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                    </select>
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-left text-sm">
                    <caption class="sr-only">{{ $heading }}</caption>
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Name</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Email</th>
                            @if ($audience === 'staff')
                                <th scope="col" class="px-4 py-3 font-semibold">Role</th>
                            @endif
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Added</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($accounts as $account)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium whitespace-nowrap sm:px-6">
                                    <a href="{{ route($routeBase.'.show', $account) }}" class="hover:text-brand">{{ $account->name }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $account->email }}</td>
                                @if ($audience === 'staff')
                                    <td class="px-4 py-3.5">{{ $account->role?->role_name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3.5">
                                    <span class="badge {{ $statusStyles[$account->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($account->status) }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-navy/70">{{ $account->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route($routeBase.'.show', $account) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route($routeBase.'.edit', $account) }}" class="ml-3 font-semibold text-brand hover:text-brand-dark">Edit</a>
                                    @if (auth()->id() !== $account->id)
                                        <form method="POST" action="{{ route($routeBase.'.destroy', $account) }}" class="inline" onsubmit="return confirm(@js('Delete '.$account->name.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-3 font-semibold text-red-700 hover:text-red-800">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $audience === 'staff' ? 6 : 5 }}" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No {{ $singular }} records match this search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $accounts])
        </section>
    </div>
@endsection
