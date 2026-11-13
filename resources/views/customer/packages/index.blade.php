@extends('layouts.public')

@section('document_title', 'Packages – BacklinkPro')

@section('meta_description', 'Display backlink packages from BacklinkPro: Starter, Growth, Professional and Enterprise.')

@section('content')
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Packages</span>
            </nav>
            <h1 class="mt-4 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[44px]">Packages</h1>
            <p class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">Choose a display backlink package. Pricing is confirmed when you order.</p>
            <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2">
                @foreach (\App\Support\PublicPackages::groups() as $group)
                    <section class="rounded-[20px] border border-[#E3E5E8] bg-white p-6" aria-labelledby="package-group-{{ \Illuminate\Support\Str::slug($group['title']) }}">
                        <h2 id="package-group-{{ \Illuminate\Support\Str::slug($group['title']) }}" class="font-display text-[22px] font-extrabold text-navy">{{ $group['title'] }}</h2>
                        @foreach ($group['sections'] as $section)
                            @if ($section['title'])
                                <h3 class="mt-4 text-[15px] font-extrabold text-[#C0340D]">{{ $section['title'] }}</h3>
                            @endif
                            <ul class="mt-3 space-y-1">
                                @foreach ($section['items'] as $item)
                                    <li id="{{ $item['slug'] }}" class="flex min-h-11 items-center gap-3 rounded-xl px-2 text-[15px] font-bold text-navy">
                                        @if ($item['flag'])
                                            <span class="inline-flex shrink-0 overflow-hidden rounded-[2px] ring-1 ring-black/10" style="width:20px;height:14px">
                                                <x-ui.flag :code="$item['flag']" width="20" height="14" class="h-full w-full" />
                                            </span>
                                        @endif
                                        {{ $item['label'] }}
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </section>
                @endforeach
            </div>
        </div>
    </section>
@endsection
