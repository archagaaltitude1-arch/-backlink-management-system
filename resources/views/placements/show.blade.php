@extends($layout)

@section('title', $article->title)

@section('content')
    <article class="mx-auto max-w-3xl space-y-6">
        <div>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Published placement</p>
            <h1 class="mt-2 text-3xl font-semibold">{{ $article->title }}</h1>
            <p class="mt-2 text-sm text-navy/60">
                {{ $article->orderItem?->website?->domain ?? 'Publisher' }}
                · Anchor: {{ $article->orderItem?->anchor_text ?: 'Not set' }}
            </p>
        </div>
        <div class="panel space-y-4 p-6 text-sm leading-7">
            @foreach ($article->paragraphs() as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
        @if ($article->orderItem?->target_url)
            <p class="text-xs break-all text-navy/55">Target: {{ $article->orderItem->target_url }}</p>
        @endif
    </article>
@endsection
