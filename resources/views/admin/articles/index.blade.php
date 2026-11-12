@extends('layouts.admin')

@section('title', 'Blog Approvals')

@section('content')
<div class="space-y-6">
    <div class="panel border-l-4 border-l-brand p-6 sm:p-8">
        <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin approval</p>
        <h1 class="mt-1 text-2xl font-bold text-navy">Blog Approvals</h1>
        <p class="mt-1.5 max-w-2xl text-sm text-navy/70">
            This admin blog section is separate from the SEO staff Articles page. SEO staff send a blog from their own page. Review, reject, or approve it here. Approval does not publish the blog.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel border-t-4 border-t-brand p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">All blogs</p>
            <p class="mt-1 font-display text-3xl font-bold text-navy">{{ $counts['total'] }}</p>
        </div>
        <div class="panel border-t-4 border-t-amber-500 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Awaiting approval</p>
            <p class="mt-1 font-display text-3xl font-bold text-amber-600">{{ $counts['pending_admin'] }}</p>
        </div>
        <div class="panel border-t-4 border-t-emerald-500 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Approved</p>
            <p class="mt-1 font-display text-3xl font-bold text-emerald-600">{{ $counts['admin_approved'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Waiting for SEO staff to publish</p>
        </div>
        <div class="panel border-t-4 border-t-sky-500 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Published by staff</p>
            <p class="mt-1 font-display text-3xl font-bold text-sky-600">{{ $counts['published'] }}</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($articles as $article)
            @php
                $item = $article->orderItem;
                $badgeClasses = match ($article->status) {
                    'admin_approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                    'published' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                    'in_review' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                    'corrections_requested' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                    'rejected' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                    default => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                };
            @endphp
            <article class="panel p-5 sm:p-6">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1 space-y-4">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="rounded-lg bg-navy/5 px-2.5 py-1 font-mono text-xs font-bold text-navy">{{ $item?->order?->order_number }}</span>
                            <span class="badge {{ $badgeClasses }}">{{ $article->statusLabel() }}</span>
                            <span class="badge bg-canvas text-navy/70">Staff Prepared</span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold leading-snug text-navy sm:text-lg">{{ $article->title }}</h2>
                            <p class="mt-1.5 text-xs leading-relaxed text-navy/70">{{ \Illuminate\Support\Str::limit($article->body, 280) }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-3 rounded-xl border border-navy/5 bg-canvas/60 p-4 sm:grid-cols-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">SEO staff</p>
                                <p class="mt-0.5 text-sm font-semibold text-navy">{{ $article->staff?->name ?? 'SEO staff' }}</p>
                                <p class="mt-0.5 text-xs text-navy/60">{{ number_format($article->wordCount()) }} words</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Publisher</p>
                                <p class="mt-0.5 text-sm font-semibold text-navy">{{ $item?->website?->domain ?? 'No publisher selected' }}</p>
                                <p class="mt-0.5 text-xs text-navy/60">{{ $item?->order?->customer?->name ?? 'Guest checkout' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Target URL & Anchor</p>
                                <p class="mt-0.5"><span class="inline-block rounded bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">"{{ $item?->anchor_text }}"</span></p>
                                <p class="mt-1 break-all text-xs text-navy/75">{{ $item?->target_url }}</p>
                            </div>
                        </div>
                        @if ($item?->requirements)
                            <div class="rounded-xl border border-navy/10 bg-white p-3.5 text-xs text-navy/75">
                                <span class="font-semibold text-navy">Customer requirements:</span>
                                {{ $item->requirements }}
                            </div>
                        @endif
                        @if ($article->staff_notes)
                            <div class="rounded-xl border border-navy/10 bg-white p-3.5 text-xs text-navy/75">
                                <span class="font-semibold text-navy">Staff notes:</span>
                                {{ $article->staff_notes }}
                            </div>
                        @endif
                        <div class="space-y-3 text-sm leading-6 text-navy/85">
                            @foreach ($article->paragraphs() as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>

                    <div class="w-full shrink-0 space-y-3 rounded-xl border border-navy/10 bg-canvas/50 p-4 lg:w-80">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-navy">Article Review</h3>
                        @if ($article->status !== 'published')
                            @if ($article->status === 'admin_approved')
                                <p class="rounded-xl bg-emerald-50 px-3 py-2 text-xs leading-5 text-emerald-800 ring-1 ring-emerald-200">
                                    Approved. Waiting for {{ $article->staff?->name ?? 'SEO staff' }} to publish.
                                </p>
                            @endif
                            <form method="POST" action="{{ route('admin.articles.decide', $article) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-navy/55" for="feedback-{{ $article->id }}">Reviewer feedback</label>
                                    <textarea id="feedback-{{ $article->id }}" name="feedback" rows="3" placeholder="Add editorial notes. Required when you reject." class="block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ (string) old('_article_id') === (string) $article->id ? old('feedback') : '' }}</textarea>
                                </div>
                                <input type="hidden" name="_article_id" value="{{ $article->id }}">
                                <div class="grid gap-2">
                                    <button type="submit" name="decision" value="review" class="btn w-full cursor-pointer bg-white py-2 text-xs text-navy ring-1 ring-navy/15 hover:bg-canvas">Review</button>
                                    <button type="submit" name="decision" value="rejected" class="btn w-full cursor-pointer bg-rose-50 py-2 text-xs text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">Reject</button>
                                    <button type="submit" name="decision" value="approve" class="btn-primary w-full cursor-pointer py-2 text-xs">Approval</button>
                                </div>
                                <p class="text-[11px] leading-5 text-navy/55">Review, reject, and approval are saved to the blog record. Approval does not publish the blog.</p>
                            </form>
                            @if ($article->latestReview)
                                <p class="text-xs leading-5 text-navy/70">
                                    Last saved decision: {{ $article->latestReview->decisionLabel() }}
                                    @if ($article->latestReview->reviewed_at)
                                        · {{ $article->latestReview->reviewed_at->format('M j, Y g:i A') }}
                                    @endif
                                </p>
                                @if ($article->latestReview->feedback)
                                    <p class="text-xs leading-5 text-navy/70">{{ $article->latestReview->feedback }}</p>
                                @endif
                            @endif
                        @elseif ($article->status === 'published')
                            <p class="text-xs leading-5 text-navy/70">Published by SEO staff.</p>
                            @if ($article->published_url)
                                <a href="{{ $article->published_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-xs font-semibold text-brand hover:underline">{{ $article->published_url }}</a>
                            @endif
                        @else
                            <p class="text-xs leading-5 text-navy/70">{{ $article->statusLabel() }}</p>
                            @if ($article->feedback)
                                <p class="text-xs leading-5 text-navy/70">{{ $article->feedback }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="panel p-10 text-center">
                <p class="text-sm font-semibold text-navy">No blogs are waiting for approval.</p>
                <p class="mt-1 text-xs text-navy/60">SEO staff send blogs here. Customers cannot post blogs.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
