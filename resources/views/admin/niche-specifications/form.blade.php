@extends('layouts.admin')

@section('title', $specification->exists ? 'Edit specification' : 'Add specification')

@section('content')
    @php $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30'; @endphp
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <a href="{{ route('admin.niches') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Niche Management</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $specification->exists ? 'Edit specification' : 'Add specification' }}</h1>
            <p class="mt-2 text-sm leading-6 text-navy/70">Specifications are shared filters used with niches. They are not tied to one niche.</p>
        </section>
        <form method="POST" action="{{ $specification->exists ? route('admin.niche-specifications.update', $specification) : route('admin.niche-specifications.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($specification->exists)
                @method('PUT')
            @endif
            <div>
                <label for="specification_name" class="text-sm font-medium">Specification name</label>
                <input id="specification_name" name="specification_name" type="text" value="{{ old('specification_name', $specification->specification_name) }}" required maxlength="150" class="{{ $input }}">
                @error('specification_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="field_type" class="text-sm font-medium">Field type</label>
                    <select id="field_type" name="field_type" required class="{{ $input }}">
                        @foreach (['number', 'text', 'boolean', 'select'] as $option)
                            <option value="{{ $option }}" @selected(old('field_type', $specification->field_type ?: 'number') === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                    @error('field_type') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="text-sm font-medium">Status</label>
                    <select id="status" name="status" required class="{{ $input }}">
                        @foreach (['active', 'inactive'] as $option)
                            <option value="{{ $option }}" @selected(old('status', $specification->status ?: 'active') === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="min_value" class="text-sm font-medium">Minimum value</label>
                    <input id="min_value" name="min_value" type="number" step="0.01" value="{{ old('min_value', $specification->min_value) }}" class="{{ $input }}">
                    @error('min_value') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_value" class="text-sm font-medium">Maximum value</label>
                    <input id="max_value" name="max_value" type="number" step="0.01" value="{{ old('max_value', $specification->max_value) }}" class="{{ $input }}">
                    @error('max_value') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $specification->exists ? 'Save changes' : 'Add specification' }}</button>
                <a href="{{ route('admin.niches') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
