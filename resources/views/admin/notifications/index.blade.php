@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        @include('admin.niches.partials.alerts')
        <section class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Notifications</h1>
                <p class="mt-2 text-sm leading-6 text-navy/70">Updates for this admin account. {{ $unread }} unread.</p>
            </div>
        </section>
        <section class="panel divide-y divide-navy/10">
            @forelse ($notifications as $notification)
                <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold">{{ $notification->data['title'] ?? 'Update' }}</h2>
                            @if ($notification->read_at === null)
                                <span class="badge bg-brand/10 text-brand">Unread</span>
                            @else
                                <span class="badge bg-canvas text-navy/60">Read</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-navy/70">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-2 text-xs text-navy/45">{{ $notification->created_at?->format('M j, Y g:i A') }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        @if (! empty($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}" class="text-sm font-semibold text-brand">Open</a>
                        @endif
                        @if ($notification->read_at === null)
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-navy">Mark read</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="px-5 py-8 text-sm text-navy/60">No admin notifications yet.</p>
            @endforelse
        </section>
        <p class="text-sm text-navy/60">Customer support requests are not stored in this system, so none are listed here.</p>
        @include('admin.partials.pagination', ['records' => $notifications])
    </div>
@endsection
