@extends('layouts.public')

@section('document_title', 'Niches – BacklinkPro')

@section('meta_description', 'Browse publisher websites by niche for guest posts, contextual links, niche edits and text links.')

@section('content')
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto px-6 py-14 lg:py-[88px]">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Niches</span>
            </nav>
            <h1 class="mt-4 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[44px]">Publisher Websites by Niche</h1>
            <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">Choose a niche to see the publisher websites that accept guest posts, contextual links, niche edits and text links.</p>
            <div class="mt-8 grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($niches as $niche)
                    <a href="{{ route('niches.show', $niche['slug']) }}" class="flex h-full min-h-14 items-center justify-between gap-4 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 font-bold text-navy hover:border-[#D63A10] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                        <span>{{ $niche['name'] }}</span>
                        <span class="min-w-8 shrink-0 rounded-full bg-[#FFF1EC] px-2.5 py-1 text-right text-xs font-bold text-[#C0340D] tabular-nums">{{ $niche['total'] }}</span>
                    </a>
                @empty
                    <p class="text-[#4A5560]">No niches are listed yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
