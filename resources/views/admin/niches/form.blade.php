@extends('layouts.admin')

@section('title', $niche->exists ? 'Edit niche' : 'Add niche')

@section('content')
    @php
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/30';
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_18px_40px_rgba(21,32,41,0.16)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.28),transparent_46%)] p-6 sm:p-8">
                <a href="{{ route('admin.niches') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Niche Management</a>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $niche->exists ? 'Edit niche' : 'Add niche' }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-white/75">{{ $niche->exists ? 'Update the name and description.' : 'Add an industry category for publisher websites.' }}</p>
            </div>
        </section>
        <form method="POST" action="{{ $niche->exists ? route('admin.niches.update', $niche) : route('admin.niches.store') }}" class="space-y-6">
            @csrf
            @if ($niche->exists)
                @method('PUT')
            @endif
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Category</p>
                    <h2 class="mt-1 text-lg font-semibold">Niche details</h2>
                </div>
                <div class="space-y-5 p-6 sm:p-8">
                    <div>
                        <label for="niche_name" class="text-sm font-medium">Niche name</label>
                        <input id="niche_name" name="niche_name" type="text" value="{{ old('niche_name', $niche->niche_name) }}" required maxlength="150" placeholder="Technology" class="{{ $input }}">
                        @error('niche_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="text-sm font-medium">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Devices, software products, and technology news." class="{{ $input }}">{{ old('description', $niche->description) }}</textarea>
                        @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $niche->exists ? 'Save changes' : 'Add niche' }}</button>
                <a href="{{ $niche->exists ? route('admin.niches.show', $niche) : route('admin.niches') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
