@extends('layouts.admin')

@section('title', $website->exists ? 'Edit website' : 'Add website')

@section('content')
    @php
        $metric = $website->exists ? $website->seoMetric : null;
        $selected = old('niche_ids', $website->exists ? $website->niches->modelKeys() : []);
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/30';
        $metricPlaceholders = [
            'DR' => '45',
            'DA' => '38',
            'TF' => '22',
            'CF' => '30',
            'traffic' => '12500',
            'spam_score' => '3',
            'backlinks' => '840',
        ];
        $metricTones = [
            'DR' => ['label' => 'Domain Rating', 'bar' => 'bg-brand', 'chip' => 'bg-brand/10 text-brand'],
            'DA' => ['label' => 'Domain Authority', 'bar' => 'bg-navy', 'chip' => 'bg-navy/10 text-navy'],
            'TF' => ['label' => 'Trust Flow', 'bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-50 text-emerald-700'],
            'CF' => ['label' => 'Citation Flow', 'bar' => 'bg-sky-500', 'chip' => 'bg-sky-50 text-sky-700'],
            'traffic' => ['label' => 'Traffic', 'bar' => 'bg-amber-500', 'chip' => 'bg-amber-50 text-amber-800'],
            'spam_score' => ['label' => 'Spam score', 'bar' => 'bg-rose-500', 'chip' => 'bg-rose-50 text-rose-700'],
            'backlinks' => ['label' => 'Backlinks', 'bar' => 'bg-violet-500', 'chip' => 'bg-violet-50 text-violet-700'],
        ];
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_18px_40px_rgba(21,32,41,0.16)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.28),transparent_46%)] p-6 sm:p-8">
                <a href="{{ route('admin.websites') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Website Management</a>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $website->exists ? 'Edit website' : 'Add website' }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-white/75">Register the publisher, choose its niches, and record the SEO metrics used for matching.</p>
            </div>
        </section>
        <form method="POST" action="{{ $website->exists ? route('admin.websites.update', $website) : route('admin.websites.store') }}" class="space-y-6">
            @csrf
            @if ($website->exists)
                @method('PUT')
            @endif
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Publisher</p>
                    <h2 class="mt-1 text-lg font-semibold">Website details</h2>
                </div>
                <div class="space-y-5 p-6 sm:p-8">
                    <div>
                        <label for="website_name" class="text-sm font-medium">Website name</label>
                        <input id="website_name" name="website_name" type="text" value="{{ old('website_name', $website->website_name) }}" required placeholder="Farm Pulse Journal" class="{{ $input }}">
                        @error('website_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="domain" class="text-sm font-medium">Domain</label>
                            <input id="domain" name="domain" type="text" value="{{ old('domain', $website->domain) }}" required placeholder="farmpulse.example" class="{{ $input }}">
                            @error('domain') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="url" class="text-sm font-medium">URL</label>
                            <input id="url" name="url" type="text" value="{{ old('url', $website->url) }}" required placeholder="https://farmpulse.example" class="{{ $input }}">
                            @error('url') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="price" class="text-sm font-medium">Price</label>
                            <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $website->exists ? $website->price : '') }}" required placeholder="120.00" class="{{ $input }}">
                            @error('price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="credit_cost" class="text-sm font-medium">Credit cost</label>
                            <input id="credit_cost" name="credit_cost" type="number" min="0" step="1" value="{{ old('credit_cost', $website->exists ? $website->credit_cost : '') }}" required placeholder="2" class="{{ $input }}">
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
                </div>
            </section>
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-sky-500 bg-gradient-to-r from-sky-50 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-sky-700 uppercase">Categories</p>
                    <h2 class="mt-1 text-lg font-semibold">Niches</h2>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($niches as $niche)
                            <label class="flex items-center gap-2.5 rounded-xl border border-transparent bg-canvas px-3 py-2.5 text-sm transition has-[:checked]:border-brand has-[:checked]:bg-brand/10 has-[:checked]:font-medium">
                                <input type="checkbox" name="niche_ids[]" value="{{ $niche->id }}" class="size-4 accent-brand" @checked(in_array($niche->id, array_map('intval', (array) $selected), true))>
                                {{ $niche->niche_name }}
                            </label>
                        @endforeach
                    </div>
                    @error('niche_ids') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </section>
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-emerald-500 bg-gradient-to-r from-emerald-50 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-emerald-700 uppercase">Authority</p>
                    <h2 class="mt-1 text-lg font-semibold">SEO metrics</h2>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
                    @foreach ($metricTones as $field => $tone)
                        <div class="overflow-hidden rounded-xl bg-[#F7F8FA] ring-1 ring-navy/10">
                            <div class="h-1 {{ $tone['bar'] }}"></div>
                            <div class="p-4">
                                <label for="{{ $field }}" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $tone['chip'] }}">{{ $tone['label'] }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" step="{{ in_array($field, ['traffic', 'backlinks'], true) ? '1' : '0.01' }}" value="{{ old($field, $website->exists ? ($metric?->{$field} ?? 0) : '') }}" required placeholder="{{ $metricPlaceholders[$field] }}" class="{{ $input }}">
                                @error($field) <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $website->exists ? 'Save changes' : 'Add website' }}</button>
                <a href="{{ $website->exists ? route('admin.websites.show', $website) : route('admin.websites') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
