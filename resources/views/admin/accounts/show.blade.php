@extends('layouts.admin')

@section('title', $account->name)

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
                <a href="{{ route($routeBase) }}" class="text-xs font-semibold tracking-wider text-brand uppercase">{{ $heading }}</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $account->name }}</h1>
                <p class="mt-2 text-sm text-navy/70">{{ $account->email }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route($routeBase.'.edit', $account) }}" class="btn-primary">Edit</a>
                @if (auth()->id() !== $account->id)
                    <form method="POST" action="{{ route($routeBase.'.destroy', $account) }}" onsubmit="return confirm(@js('Delete '.$account->name.'?'))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">Delete</button>
                    </form>
                @endif
            </div>
        </section>

        @include('admin.niches.partials.alerts')

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Role</p>
                <p class="mt-3 text-lg font-semibold">{{ $account->role?->role_name ?? '—' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Status</p>
                <p class="mt-3"><span class="badge {{ $statusStyles[$account->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($account->status) }}</span></p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Added</p>
                <p class="mt-3 text-lg font-semibold">{{ $account->created_at?->format('M j, Y') ?? '—' }}</p>
            </article>
        </section>
    </div>
@endsection
