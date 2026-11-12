@props([
    'seed',
    'color' => '#E24B4B',
])

@php
    $state = crc32((string) $seed);
    $samples = [];

    for ($point = 0; $point < 16; $point++) {
        $state = (int) (($state * 1103515245 + 12345) & 0x7fffffff);
        $samples[] = ($state % 100) / 100;
    }

    $smooth = [];

    foreach ($samples as $index => $sample) {
        $previous = $samples[max(0, $index - 1)];
        $next = $samples[min(count($samples) - 1, $index + 1)];
        $smooth[] = ($previous + $sample * 2 + $next) / 4;
    }

    $width = 88;
    $height = 34;
    $step = $width / (count($smooth) - 1);
    $coordinates = [];

    foreach ($smooth as $index => $sample) {
        $coordinates[] = round($index * $step, 1).','.round(3 + (1 - $sample) * 24, 1);
    }

    $line = implode(' ', $coordinates);
    $area = '0,'.$height.' '.$line.' '.$width.','.$height;
@endphp

<svg {{ $attributes->merge(['class' => 'nd-spark', 'viewBox' => '0 0 88 34', 'width' => '88', 'height' => '34']) }} aria-hidden="true" focusable="false">
    <polygon points="{{ $area }}" fill="{{ $color }}" opacity="0.2"></polygon>
    <polyline points="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="1.7" stroke-linejoin="round" stroke-linecap="round"></polyline>
</svg>
