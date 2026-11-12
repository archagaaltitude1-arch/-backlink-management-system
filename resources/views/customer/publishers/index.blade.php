@extends('layouts.public')

@php
    use Illuminate\Support\Str;

    $chartRows = [];

    foreach (array_slice($rows, 0, 8) as $row) {
        $value = $row['dr'] ?? null;
        $number = is_numeric($value) ? (int) $value : 0;
        $slug = Str::slug((string) ($row['domain'] ?? 'site'));
        $chartRows[] = [
            'label' => (string) ($row['domain'] ?? ''),
            'value' => $number,
            'text' => is_numeric($value) ? (string) $number : '',
            'full' => is_numeric($value) ? number_format($number) : '',
            'target' => 'site-row-'.($slug !== '' ? $slug : 'site'),
        ];
    }

    $chartMax = 0;

    foreach ($chartRows as $bar) {
        $chartMax = max($chartMax, $bar['value']);
    }
@endphp

@section('document_title', 'Guest Posting Websites – BacklinkPro')

@section('meta_description', 'Browse every publisher website that accepts guest posts, then compare stored Domain Rating, traffic and price.')

@section('content')
    <section class="bg-[#F2F2F2]">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-16">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Publisher list</span>
            </nav>

            <div class="mt-8 flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0 max-w-[760px]">
                    <p class="text-[13px] font-extrabold tracking-wide text-[#C0340D] uppercase">Publisher list</p>
                    <h1 class="mt-2 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[40px]">Guest posting websites</h1>
                    <p class="mt-4 text-[17px] leading-8 text-[#4A5560]">These are the posting websites in the publisher list. Every stored publisher is included, with the highest stored Domain Rating first.</p>
                    <p class="mt-4 text-[17px] leading-8 text-[#4A5560]">The chart compares the Domain Rating stored on these listings. A higher bar is a higher stored figure, not a ranking forecast.</p>
                    @if ($demo)
                        <p class="mt-4 rounded-xl bg-[#FFF1EC] px-4 py-3 text-[15px] leading-6 text-[#C0340D]">Sample data for preview. These figures belong to fictional .example publishers.</p>
                    @endif
                </div>
                <button type="button" id="create-project" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-5 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" aria-expanded="false" aria-controls="create-project-panel">Create project</button>
            </div>

            <div id="create-project-panel" class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6" hidden>
                <h2 class="font-display text-[22px] font-extrabold text-navy">Create project</h2>
                <p class="mt-2 max-w-[640px] text-[15px] leading-7 text-[#4A5560]">Name the project, then choose publishers from the list on this page.</p>
                <form id="create-project-form" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="project-name" class="text-[13px] font-bold text-navy">Project name</label>
                        <input id="project-name" name="project_name" type="text" required maxlength="80" class="mt-2 h-12 w-full rounded-xl border border-[#E3E5E8] px-4 text-[15px] text-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                    </div>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-5 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Create project</button>
                </form>
                <p id="create-project-status" class="mt-3 text-[15px] leading-6 text-[#C0340D]" role="status"></p>
            </div>

            <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                <h2 class="font-display text-[22px] font-extrabold text-navy">Preview</h2>
                <p class="mt-1 text-[15px] leading-6 text-[#4A5560]">Domain Rating from the publishers on this list.</p>
                @if ($chartRows === [] || $chartMax === 0)
                    <p class="mt-4 text-[15px] leading-7 text-[#4A5560]">No stored figures to chart for this list yet.</p>
                @else
                    <ul class="mt-5 space-y-2">
                        @foreach ($chartRows as $bar)
                            <li>
                                <button type="button" class="site-chart-bar flex min-h-11 w-full flex-col justify-center rounded-xl px-2 text-left hover:bg-[#FFF1EC] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" data-target="{{ $bar['target'] }}" aria-label="Show {{ $bar['label'] }}, {{ $bar['full'] }}">
                                    <span class="flex items-baseline justify-between gap-3 text-[14px]">
                                        <span class="min-w-0 truncate font-bold text-navy">{{ $bar['label'] }}</span>
                                        <span class="shrink-0 text-[#4A5560]">{{ $bar['text'] }}</span>
                                    </span>
                                    <span class="mt-1 block h-2 overflow-hidden rounded-full bg-[#F2F2F2]" aria-hidden="true">
                                        <span class="block h-full rounded-full bg-[#D63A10]" style="width: {{ $bar['value'] > 0 ? max(4, (int) round($bar['value'] / $chartMax * 100)) : 0 }}%"></span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                <h2 class="font-display text-[22px] font-extrabold text-navy">Categories</h2>
                @if ($categories === [])
                    <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">No categories are stored yet.</p>
                @else
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($categories as $name => $slug)
                            <li>
                                <a href="{{ route('niches.show', $slug) }}" class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] bg-white px-4 text-[14px] font-bold text-navy hover:bg-[#FFF1EC] hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($rows === [])
                <p class="mt-8 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 text-[15px] leading-7 text-[#4A5560]">No publisher is listed yet.</p>
            @else
                <div class="mt-8">
                    @include('customer.sites.table', ['rows' => $rows, 'demo' => $demo])
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        var createProject = document.getElementById('create-project');
        var createPanel = document.getElementById('create-project-panel');
        var createForm = document.getElementById('create-project-form');
        var createStatus = document.getElementById('create-project-status');

        if (createProject && createPanel) {
            createProject.addEventListener('click', function () {
                var open = createPanel.hasAttribute('hidden');
                if (open) {
                    createPanel.removeAttribute('hidden');
                } else {
                    createPanel.setAttribute('hidden', '');
                }
                createProject.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    document.getElementById('project-name').focus();
                }
            });
        }

        if (createForm) {
            createForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var name = document.getElementById('project-name').value.trim();
                createStatus.textContent = name + ' is ready on this page. Choose publishers from the list below.';
            });
        }

        document.querySelectorAll('.site-chart-bar').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = document.getElementById(button.getAttribute('data-target'));
                if (!target) {
                    return;
                }
                document.querySelectorAll('.is-chart-target').forEach(function (node) {
                    node.classList.remove('is-chart-target');
                });
                target.classList.add('is-chart-target');
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    </script>
@endpush
