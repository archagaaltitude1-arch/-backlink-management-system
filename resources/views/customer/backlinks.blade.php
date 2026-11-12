@extends('layouts.customer')

@section('title', 'My Backlinks')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">My account</p>
            <h1 class="mt-2 text-3xl font-semibold">My backlinks</h1>
            <p class="mt-2 text-sm text-navy/60">Review each published placement and approve it, or send it back to the assigned staff.</p>
        </div>
        @if (session('success'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">{{ session('success') }}</p>
        @endif
        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-left text-sm">
                    <thead class="bg-navy text-white">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Project</th>
                            <th class="px-5 py-4 font-semibold">Target URL</th>
                            <th class="px-5 py-4 font-semibold">Published URL</th>
                            <th class="px-5 py-4 font-semibold">Approval</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backlinks as $backlink)
                            <tr class="border-b border-navy/10 align-top">
                                <td class="px-5 py-4 font-medium">
                                    {{ $backlink->name }}
                                    <p class="mt-1 text-xs font-normal text-navy/55">{{ $backlink->order?->order_number }}</p>
                                </td>
                                <td class="max-w-xs px-5 py-4 break-all">{{ $backlink->target_url }}</td>
                                <td class="max-w-xs px-5 py-4 break-all">
                                    @php($placementUrl = $backlink->orderItem?->article?->viewUrl())
                                    <a class="text-brand hover:underline" href="{{ $placementUrl ?: $backlink->published_url }}" @if (! $placementUrl || $placementUrl === $backlink->published_url) target="_blank" rel="noopener noreferrer" @endif>{{ $placementUrl ?: $backlink->published_url }}</a>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $backlink->verification_status)) }}</p>
                                    @if ($backlink->verification_status !== 'approved')
                                        <form method="POST" action="{{ route('customer.backlinks.decide', $backlink) }}" class="mt-3 space-y-2">
                                            @csrf
                                            <label for="feedback-{{ $backlink->id }}" class="sr-only">Feedback</label>
                                            <textarea id="feedback-{{ $backlink->id }}" name="feedback" rows="2" placeholder="What should staff change?" class="w-full rounded-xl border border-navy/15 px-3 py-2 text-xs outline-none focus:border-brand focus:ring-2 focus:ring-brand/30"></textarea>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="submit" name="decision" value="approve" class="btn-primary px-3 py-1.5 text-xs">Approve</button>
                                                <button type="submit" name="decision" value="changes" class="rounded-xl bg-canvas px-3 py-1.5 text-xs font-semibold text-navy">Request changes</button>
                                            </div>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-navy/55">Published backlinks will appear here for your approval.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $backlinks->links() }}</div>
        </section>
    </div>
@endsection
