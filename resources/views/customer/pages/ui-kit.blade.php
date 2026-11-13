@extends('layouts.public')

@section('title', 'UI kit')

@section('meta_description', 'Shared public components for the BacklinkPro customer website.')

@section('content')
    @php
        $errorBag = $errors->getBag('default');
        $errorBag->add('sample_email', 'Enter a valid email address.');
        $errors->put('default', $errorBag);

        $websites = collect();

        try {
            if (class_exists(\App\Models\Website::class) && \Illuminate\Support\Facades\Schema::hasTable('websites')) {
                $websites = \App\Models\Website::query()
                    ->where('status', 'active')
                    ->take(3)
                    ->get();
            }
        } catch (\Throwable $exception) {
            $websites = collect();
        }
    @endphp

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
            <x-ui.section-heading
                eyebrow="Design system"
                title="Public UI kit"
                subtitle="Shared pieces for the customer website. Surfaces stay on white, canvas, navy, and brand."
                level="h1"
            />
        </div>
    </section>

    <section class="bg-canvas" aria-labelledby="buttons-heading">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="buttons-heading" class="font-display text-2xl font-bold text-navy">Buttons</h2>
            <x-ui.card title="Variants and sizes">
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button variant="primary">Primary</x-ui.button>
                    <x-ui.button variant="navy">Navy</x-ui.button>
                    <x-ui.button variant="outline">Outline</x-ui.button>
                    <x-ui.button variant="primary" size="sm">Small</x-ui.button>
                    <x-ui.button variant="primary" size="md">Medium</x-ui.button>
                    <x-ui.button variant="navy" size="lg">Large</x-ui.button>
                    <x-ui.button variant="outline" href="#main">Link button</x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </section>

    <section class="bg-white" aria-labelledby="heading-demo">
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="heading-demo" class="font-display text-2xl font-bold text-navy">Section heading</h2>
            <x-ui.section-heading
                eyebrow="Marketplace"
                title="Websites ready for a guest post"
                subtitle="Filter by niche, authority, and traffic before you add a site to the cart."
            />
        </div>
    </section>

    <section class="bg-canvas" aria-labelledby="metrics-heading">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="metrics-heading" class="font-display text-2xl font-bold text-navy">Metric badges</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.metric-badge label="DR" value="48" />
                <x-ui.metric-badge label="DA" value="52" />
                <x-ui.metric-badge label="Traffic" value="50K" />
                <x-ui.metric-badge label="Spam" value="2" />
                <x-ui.metric-badge label="Spam" value="5" />
                <x-ui.metric-badge label="Spam" value="12" />
            </div>
        </div>
    </section>

    <section class="bg-white" aria-labelledby="cards-heading">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="cards-heading" class="font-display text-2xl font-bold text-navy">Card</h2>
            <x-ui.card title="Plain card">
                <p class="text-sm leading-6 text-navy/70">Use cards for grouped content. The panel class supplies the white surface, rounded corners, and soft shadow.</p>
            </x-ui.card>
        </div>
    </section>

    <section class="bg-canvas" aria-labelledby="websites-heading">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="websites-heading" class="font-display text-2xl font-bold text-navy">Website cards</h2>
            @if ($websites->isEmpty())
                <x-ui.empty-state
                    title="No active websites yet"
                    message="Publisher websites marked active will show here with domain, niche, DR, DA, traffic, and price."
                />
            @else
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($websites as $website)
                        <x-ui.website-card :website="$website" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="bg-white" aria-labelledby="empty-heading">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="empty-heading" class="font-display text-2xl font-bold text-navy">Empty state</h2>
            <x-ui.empty-state
                title="No websites match these filters"
                message="Clear a filter or search a different niche to see partner sites again."
            >
                <x-ui.button variant="navy" type="button">Clear filters</x-ui.button>
            </x-ui.empty-state>
        </div>
    </section>

    <section class="bg-canvas" aria-labelledby="form-heading">
        <div class="mx-auto max-w-6xl space-y-4 px-4 py-12 sm:px-6 sm:py-16">
            <h2 id="form-heading" class="font-display text-2xl font-bold text-navy">Form input</h2>
            <x-ui.card title="Label, hint, and error">
                <form class="grid gap-4 sm:grid-cols-2" action="{{ route('ui.kit') }}" method="GET">
                    <x-ui.form-input
                        label="Website name"
                        name="website_name"
                        hint="Shown beside the domain on a marketplace card."
                        placeholder="Northline Journal"
                        autocomplete="organization"
                    />
                    <x-ui.form-input
                        label="Contact email"
                        name="sample_email"
                        type="email"
                        value="not-an-email"
                        autocomplete="email"
                    />
                </form>
            </x-ui.card>
        </div>
    </section>
@endsection
