@props(['points' => []])

@php
    $points = array_values($points);
    $count = count($points);
    $width = 640;
    $height = 240;
    $left = 56;
    $right = 16;
    $top = 20;
    $bottom = 36;
    $plotW = $width - $left - $right;
    $plotH = $height - $top - $bottom;
    $max = max(1, ...array_map(fn (array $point) => (int) $point['value'], $points ?: [['value' => 1]]));
    $step = $count > 1 ? $plotW / ($count - 1) : 0;
    $coords = [];

    foreach ($points as $index => $point) {
        $x = $left + ($step * $index);
        $y = $top + $plotH - (($point['value'] / $max) * $plotH);
        $coords[] = ['x' => round($x, 1), 'y' => round($y, 1), 'point' => $point];
    }

    $line = implode(' ', array_map(fn (array $coord) => $coord['x'].','.$coord['y'], $coords));
    $baseY = $top + $plotH;
    $area = $coords === [] ? '' : $coords[0]['x'].','.$baseY.' '.$line.' '.$coords[$count - 1]['x'].','.$baseY;
    $first = $points[0] ?? null;
    $last = $points[$count - 1] ?? null;
    $label = ($first && $last)
        ? 'Monthly traffic from '.$first['label'].' '.\App\Support\DemoNicheData::compact($first['value']).' to '.$last['label'].' '.\App\Support\DemoNicheData::compact($last['value'])
        : 'Monthly traffic chart';
@endphp

@if ($count > 0)
    <div class="wd-chart">
        <svg viewBox="0 0 {{ $width }} {{ $height }}" width="100%" role="img" aria-label="{{ $label }}" style="display:block;max-width:100%;height:auto">
            @for ($lineIndex = 0; $lineIndex < 4; $lineIndex++)
                @php
                    $ratio = 1 - ($lineIndex / 3);
                    $y = round($top + ($plotH * (1 - $ratio)), 1);
                    $tick = (int) round($max * $ratio);
                @endphp
                <line x1="{{ $left }}" y1="{{ $y }}" x2="{{ $width - $right }}" y2="{{ $y }}" stroke="#E3E5E8" stroke-width="1" />
                <text x="{{ $left - 8 }}" y="{{ $y + 4 }}" text-anchor="end" fill="#4A5560" font-size="12" font-family="Ubuntu, sans-serif">{{ \App\Support\DemoNicheData::compact($tick) }}</text>
            @endfor
            @if ($area !== '')
                <polygon points="{{ $area }}" fill="#FF4917" fill-opacity="0.1" stroke="none" />
                <polyline points="{{ $line }}" fill="none" stroke="#FF4917" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
            @endif
            @foreach ($coords as $coord)
                <g>
                    <title>{{ $coord['point']['label'] }}: {{ \App\Support\DemoNicheData::compact($coord['point']['value']) }}</title>
                    @if ($loop->last)
                        <circle cx="{{ $coord['x'] }}" cy="{{ $coord['y'] }}" r="4" fill="#FF4917" />
                        <text x="{{ $coord['x'] }}" y="{{ max(14, $coord['y'] - 10) }}" text-anchor="middle" fill="#152029" font-size="12" font-weight="700" font-family="Ubuntu, sans-serif">{{ \App\Support\DemoNicheData::compact($coord['point']['value']) }}</text>
                    @endif
                    <text x="{{ $coord['x'] }}" y="{{ $height - 12 }}" text-anchor="middle" fill="#4A5560" font-size="12" font-family="Ubuntu, sans-serif">{{ $coord['point']['short'] }}</text>
                </g>
            @endforeach
        </svg>
        <table style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0">
            <caption>Monthly traffic</caption>
            <thead>
                <tr>
                    <th scope="col">Month</th>
                    <th scope="col">Visits</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($points as $point)
                    <tr>
                        <th scope="row">{{ $point['label'] }}</th>
                        <td>{{ \App\Support\DemoNicheData::compact($point['value']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
