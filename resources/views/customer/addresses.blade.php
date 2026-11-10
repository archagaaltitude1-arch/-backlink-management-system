@extends('layouts.customer')

@section('title', 'Billing Addresses')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">My account</p>
            <h1 class="mt-2 text-3xl font-semibold">Billing addresses</h1>
        </div>
        @if (session('success'))
            <div class="rounded-xl bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
            <section class="space-y-3">
                @forelse ($addresses as $address)
                    <article class="panel space-y-4 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="font-semibold">{{ $address->label }}</h2>
                                    @if ($address->is_default)
                                        <span class="badge bg-green-100 text-green-800">Default</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm">{{ $address->full_name }}<br>{{ $address->address_line_1 }} {{ $address->address_line_2 }}<br>{{ $address->city }} {{ $address->state }} {{ $address->postal_code }}<br>{{ $address->country }}</p>
                            </div>
                            <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn rounded-xl text-red-700 hover:bg-red-50" type="submit">Remove</button>
                            </form>
                        </div>
                        <details class="border-t border-navy/10 pt-3">
                            <summary class="cursor-pointer text-sm font-semibold text-brand">Edit address</summary>
                            <form method="POST" action="{{ route('customer.addresses.update', $address) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                                @csrf
                                @method('PATCH')
                                @foreach (['label' => 'Label', 'full_name' => 'Full name', 'company' => 'Company', 'address_line_1' => 'Address line 1', 'address_line_2' => 'Address line 2', 'city' => 'City', 'state' => 'State / Province', 'postal_code' => 'Postal code', 'country' => 'Country code'] as $name => $label)
                                    <div @if (in_array($name, ['address_line_1', 'address_line_2'])) class="sm:col-span-2" @endif>
                                        <label class="text-xs font-medium" for="{{ $name }}-{{ $address->id }}">{{ $label }}</label>
                                        <input id="{{ $name }}-{{ $address->id }}" name="{{ $name }}" value="{{ $address->{$name} }}" @if (! in_array($name, ['company', 'address_line_2', 'state'])) required @endif @if ($name === 'country') maxlength="2" @endif class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                                    </div>
                                @endforeach
                                <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="is_default" type="checkbox" value="1" @checked($address->is_default) class="rounded border-navy/30 text-brand focus:ring-brand">Set as default</label>
                                <div class="sm:col-span-2"><button class="btn-navy" type="submit">Save changes</button></div>
                            </form>
                        </details>
                    </article>
                @empty
                    <div class="panel p-8 text-center text-sm text-navy/55">No billing addresses saved.</div>
                @endforelse
            </section>

            <section class="panel h-fit p-5">
                <h2 class="font-semibold">Add an address</h2>
                <form method="POST" action="{{ route('customer.addresses.store') }}" class="mt-4 space-y-3">
                    @csrf
                    @foreach (['label' => 'Label', 'full_name' => 'Full name', 'company' => 'Company', 'address_line_1' => 'Address line 1', 'address_line_2' => 'Address line 2', 'city' => 'City', 'state' => 'State / Province', 'postal_code' => 'Postal code', 'country' => 'Country code'] as $name => $label)
                        <div>
                            <label class="text-xs font-medium" for="new-{{ $name }}">{{ $label }}</label>
                            <input id="new-{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" @if (! in_array($name, ['company', 'address_line_2', 'state'])) required @endif @if ($name === 'country') maxlength="2" placeholder="US" @endif class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                        </div>
                    @endforeach
                    <label class="flex items-center gap-2 text-sm"><input name="is_default" type="checkbox" value="1" class="rounded border-navy/30 text-brand focus:ring-brand">Set as default</label>
                    <button class="btn-primary w-full" type="submit">Save address</button>
                </form>
            </section>
        </div>
    </div>
@endsection