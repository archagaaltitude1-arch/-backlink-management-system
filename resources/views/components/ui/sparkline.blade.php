@props([
    'seed' => '',
    'color' => '#E98989',
    'points' => null,
    'label' => null,
    'flat' => false,
    'value' => null,
    'readout' => null,
])

@php
    $supplied = is_array($points) ? array_values(array_filter($points, 'is_numeric')) : [];
    $end = is_numeric($value) ? max(1, (int) $value) : null;
    $raw = [];

    if ($flat) {
        $raw = array_fill(0, 18, $end ?? 1);
    } elseif (count($supplied) >= 2) {
        $raw = array_map(fn ($point) => (float) $point, $supplied);
    } else {
        $state = abs(crc32((string) $seed));
        $target = $end ?? 100;
        $count = 18;

        for ($month = 0; $month < $count; $month++) {
            $state = (int) (($state * 1103515245 + 12345) & 0x7fffffff);
            $wave = sin(($month / 2.2) + ($state % 6)) * 0.045;
            $noise = (($state % 15) - 7) / 220;
            $drift = (($month / ($count - 1)) - 0.55) * 0.06;
            $raw[] = max(1, (int) round($target * (1 + $wave + $noise + $drift)));
        }

        if ($end !== null) {
            $raw[$count - 1] = $end;
        }
    }

    $labels = [];
    $start = now()->startOfMonth()->subMonths(count($raw) - 1);

    foreach ($raw as $index => $point) {
        $labels[] = $start->copy()->addMonths($index)->format('M');
    }

    $low = min($raw);
    $high = max($raw);
    $pad = max(($high - $low) * 0.7, $high * 0.08, 1);
    $plotLow = $low - $pad;
    $plotHigh = $high + $pad;
    $span = max(0.001, $plotHigh - $plotLow);
    $width = 72;
    $height = 22;
    $step = $width / max(1, count($raw) - 1);
    $coordinates = [];

    foreach ($raw as $index => $point) {
        $sample = ((float) $point - $plotLow) / $span;
        $coordinates[] = [round($index * $step, 1), round(2 + (1 - $sample) * 16, 1)];
    }

    $line = implode(' ', array_map(fn (array $point) => $point[0].','.$point[1], $coordinates));
    $area = '0,'.$height.' '.$line.' '.$width.','.$height;
    $encoded = array_map(fn ($point) => round((float) $point, 1), $raw);
    $showTip = $readout === null ? ! $flat : (bool) $readout;
    $figure = is_numeric($value) ? \App\Support\DemoNicheData::compact((int) $value) : null;
@endphp

<div
    class="live-chart"
    data-live-chart
    data-values="{{ implode(',', $encoded) }}"
    data-labels="{{ implode(',', $labels) }}"
    data-xs="{{ implode(',', array_column($coordinates, 0)) }}"
    data-ys="{{ implode(',', array_column($coordinates, 1)) }}"
>
    @if ($showTip)
        <span class="live-chart-tip" hidden></span>
    @endif
    <svg {{ $attributes->merge(['class' => 'nd-spark', 'viewBox' => '0 0 '.$width.' '.$height]) }} @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif tabindex="0" focusable="true">
        <polygon points="{{ $area }}" fill="{{ $color }}" opacity="0.28"></polygon>
        <polyline points="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"></polyline>
        <circle class="live-chart-dot" cx="0" cy="0" r="2.2" fill="{{ $color }}" opacity="0"></circle>
    </svg>
    @if ($figure !== null && $figure !== '')
        <p class="live-chart-figure">{{ $figure }}</p>
    @endif
</div>

@once
    <style>
        .live-chart { position: relative; display: flex; flex-direction: column; align-items: flex-start; gap: 1px; width: 72px; }
        .live-chart .nd-spark { display: block; width: 72px; height: 22px; }
        .live-chart-figure { margin: 0; color: #152029; font-size: 15px; font-weight: 800; line-height: 1.15; white-space: nowrap; }
        .live-chart-tip { position: absolute; z-index: 3; bottom: calc(100% + 4px); transform: translateX(-50%); border-radius: 6px; background: #152029; padding: 3px 6px; color: #fff; font-size: 11px; font-weight: 700; line-height: 1.2; white-space: nowrap; pointer-events: none; }
        .live-chart svg:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
    </style>
@endonce

@once
    @push('scripts')
        <script>
            (function () {
                function format(value) {
                    var number = Number(value);
                    if (!isFinite(number)) return '';
                    if (number >= 1000000) return (Math.round(number / 100000) / 10) + 'M';
                    if (number >= 1000) return (Math.round(number / 100) / 10) + 'K';
                    return String(Math.round(number));
                }

                function show(chart, index) {
                    var values = (chart.getAttribute('data-values') || '').split(',');
                    var labels = (chart.getAttribute('data-labels') || '').split(',');
                    var xs = (chart.getAttribute('data-xs') || '').split(',');
                    var ys = (chart.getAttribute('data-ys') || '').split(',');
                    var tip = chart.querySelector('.live-chart-tip');
                    var dot = chart.querySelector('.live-chart-dot');
                    if (!values[index]) return;
                    if (tip) {
                        tip.hidden = false;
                        tip.textContent = (labels[index] || '') + ' · ' + format(values[index]);
                        tip.style.left = ((Number(index) / Math.max(1, values.length - 1)) * 100) + '%';
                    }
                    if (dot && xs[index]) {
                        dot.setAttribute('cx', xs[index]);
                        dot.setAttribute('cy', ys[index]);
                        dot.setAttribute('opacity', '1');
                    }
                }

                function hide(chart) {
                    var tip = chart.querySelector('.live-chart-tip');
                    var dot = chart.querySelector('.live-chart-dot');
                    if (tip) tip.hidden = true;
                    if (dot) dot.setAttribute('opacity', '0');
                }

                document.addEventListener('pointermove', function (event) {
                    var chart = event.target.closest('[data-live-chart]');
                    if (!chart) return;
                    var svg = chart.querySelector('svg');
                    var box = svg.getBoundingClientRect();
                    var count = (chart.getAttribute('data-values') || '').split(',').length;
                    if (!count || box.width <= 0) return;
                    var ratio = Math.min(1, Math.max(0, (event.clientX - box.left) / box.width));
                    show(chart, Math.round(ratio * (count - 1)));
                });

                document.addEventListener('pointerout', function (event) {
                    var chart = event.target.closest('[data-live-chart]');
                    if (!chart || chart.contains(event.relatedTarget)) return;
                    hide(chart);
                });

                document.addEventListener('keydown', function (event) {
                    var chart = event.target.closest('[data-live-chart]');
                    if (!chart || (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight')) return;
                    var count = (chart.getAttribute('data-values') || '').split(',').length;
                    var current = Number(chart.getAttribute('data-index') || count - 1);
                    var next = event.key === 'ArrowRight' ? Math.min(count - 1, current + 1) : Math.max(0, current - 1);
                    chart.setAttribute('data-index', String(next));
                    show(chart, next);
                    event.preventDefault();
                });
            })();
        </script>
    @endpush
@endonce
