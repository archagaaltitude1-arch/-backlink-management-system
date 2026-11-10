@extends('layouts.admin')

@section('title', $niche->exists ? 'Edit niche' : 'Add niche')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Niche Management</p>
            <h1 class="mt-2 text-2xl font-semibold">{{ $niche->exists ? 'Edit niche' : 'Add niche' }}</h1>
            <p class="mt-2 text-sm leading-6 text-navy/70">{{ $niche->exists ? 'Update the name and description.' : 'Add an industry category for publisher websites.' }}</p>
        </section>

        <form method="POST" action="{{ $niche->exists ? route('admin.niches.update', $niche) : route('admin.niches.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($niche->exists)
                @method('PUT')
            @endif

            <div>
                <label for="niche_name" class="text-sm font-medium">Niche name</label>
                <input id="niche_name" name="niche_name" type="text" value="{{ old('niche_name', $niche->niche_name) }}" required maxlength="150" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                @error('niche_name')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="text-sm font-medium">Description</label>
                <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">{{ old('description', $niche->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $niche->exists ? 'Save changes' : 'Add niche' }}</button>
                <a href="{{ $niche->exists ? route('admin.niches.show', $niche) : route('admin.niches') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
