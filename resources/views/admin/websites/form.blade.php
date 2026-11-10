@extends('layouts.admin')

@section('title', $website->exists ? 'Edit website' : 'Add website')

@section('content')
    @php
        $metric = $website->exists ? $website->seoMetric : null;
        $selected = old('niche_ids', $website->exists ? $website->niches->modelKeys() : []);
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30';
    @endphp
    <div class="mx-auto max-w-3xl space-y-6">
        <section>
            <a href="{{ route('admin.websites') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Website Management</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $website->exists ? 'Edit website' : 'Add website' }}</h1>
        </section>
        <form method="POST" action="{{ $website->exists ? route('admin.websites.update', $website) : route('admin.websites.store') }}" class="space-y-6">
            @csrf
            @if ($website->exists)
                @method('PUT')
            @endif
            <section class="panel space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-semibold">Website details</h2>
                <div>
                    <label for="website_name" class="text-sm font-medium">Website name</label>
                    <input id="website_name" name="website_name" type="text" value="{{ old('website_name', $website->website_name) }}" required class="{{ $input }}">
                    @error('website_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="domain" class="text-sm font-medium">Domain</label>
                        <input id="domain" name="domain" type="text" value="{{ old('domain', $website->domain) }}" required class="{{ $input }}">
                        @error('domain') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="url" class="text-sm font-medium">URL</label>
                        <input id="url" name="url" type="text" value="{{ old('url', $website->url) }}" required class="{{ $input }}">
                        @error('url') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="price" class="text-sm font-medium">Price</label>
                        <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $website->price) }}" required class="{{ $input }}">
                        @error('price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="credit_cost" class="text-sm font-medium">Credit cost</label>
                        <input id="credit_cost" name="credit_cost" type="number" min="0" step="1" value="{{ old('credit_cost', $website->credit_cost) }}" required class="{{ $input }}">
                        @error('credit_cost') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="text-sm font-medium">Status</label>
                        <select id="status" name="status" required class="{{ $input }}">
                            @foreach (['active', 'pending', 'inactive'] as $option)
                                <option value="{{ $option }}" @selected(old('status', $website->status ?: 'active') === $option)>{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            <section class="panel p-6 sm:p-8">
                <h2 class="text-lg font-semibold">Niches</h2>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($niches as $niche)
                        <label class="flex items-center gap-2 rounded-xl bg-canvas px-3 py-2 text-sm">
                            <input type="checkbox" name="niche_ids[]" value="{{ $niche->id }}" @checked(in_array($niche->id, array_map('intval', (array) $selected), true))>
                            {{ $niche->niche_name }}
                        </label>
                    @endforeach
                </div>
                @error('niche_ids') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </section>
            <section class="panel space-y-5 p-6 sm:p-8">
                <h2 class="text-lg font-semibold">SEO metrics</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach (['DR' => 'Domain Rating', 'DA' => 'Domain Authority', 'TF' => 'Trust Flow', 'CF' => 'Citation Flow', 'traffic' => 'Traffic', 'spam_score' => 'Spam score', 'backlinks' => 'Backlinks'] as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="text-sm font-medium">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" step="{{ in_array($field, ['traffic', 'backlinks'], true) ? '1' : '0.01' }}" value="{{ old($field, $metric?->{$field} ?? 0) }}" required class="{{ $input }}">
                            @error($field) <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $website->exists ? 'Save changes' : 'Add website' }}</button>
                <a href="{{ $website->exists ? route('admin.websites.show', $website) : route('admin.websites') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
