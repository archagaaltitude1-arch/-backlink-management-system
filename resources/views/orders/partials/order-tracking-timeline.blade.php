<div>
    <ol class="hidden grid-cols-5 gap-0 sm:grid" aria-label="{{ $label }}">
        @foreach ($steps as $step)
            <li class="relative flex min-w-0 flex-col items-center text-center" @if ($step['state'] === 'current') aria-current="step" @endif>
                @unless ($loop->last)
                    <span @class([
                        'absolute top-3.5 left-1/2 h-0.5 w-full',
                        'bg-brand' => $step['reached'],
                        'bg-navy/15' => ! $step['reached'],
                    ]) aria-hidden="true"></span>
                @endunless
                <span @class([
                    'relative z-10 grid size-7 place-items-center rounded-full border-2 text-xs font-bold',
                    'border-brand bg-brand text-white' => $step['state'] === 'complete',
                    'border-brand bg-white text-brand ring-4 ring-brand/10' => $step['state'] === 'current',
                    'border-navy/20 bg-white text-navy/40' => $step['state'] === 'upcoming',
                ]) aria-hidden="true">
                    @if ($step['state'] === 'complete')
                        ✓
                    @else
                        {{ $loop->iteration }}
                    @endif
                </span>
                <div class="mt-3 px-2">
                    <p @class([
                        'text-xs font-semibold sm:text-sm',
                        'text-navy' => $step['state'] !== 'upcoming',
                        'text-navy/45' => $step['state'] === 'upcoming',
                    ])>{{ $step['label'] }}</p>
                    @if ($step['state'] === 'complete')
                        @if ($step['timestamp'])
                            <time class="mt-1 block text-[11px] leading-4 text-navy/55 sm:text-xs" datetime="{{ $step['timestamp']->toIso8601String() }}">{{ $step['timestamp']->format('M j, Y · g:i A') }}</time>
                        @else
                            <p class="mt-1 text-[11px] leading-4 text-navy/50 sm:text-xs">Time not recorded</p>
                        @endif
                    @elseif ($step['state'] === 'current')
                        <p class="mt-1 text-[11px] leading-4 text-navy/65 sm:text-xs">{{ $step['waiting'] }}</p>
                    @else
                        <p class="mt-1 text-[11px] leading-4 text-navy/45 sm:text-xs">Upcoming</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>

    <ol class="space-y-0 sm:hidden" aria-label="{{ $label }}">
        @foreach ($steps as $step)
            <li @class(['relative flex gap-3 pb-5 last:pb-0']) @if ($step['state'] === 'current') aria-current="step" @endif>
                <span class="flex w-7 shrink-0 flex-col items-center" aria-hidden="true">
                    <span @class([
                        'grid size-7 shrink-0 place-items-center rounded-full border-2 text-xs font-bold',
                        'border-brand bg-brand text-white' => $step['state'] === 'complete',
                        'border-brand bg-white text-brand ring-4 ring-brand/10' => $step['state'] === 'current',
                        'border-navy/20 bg-white text-navy/40' => $step['state'] === 'upcoming',
                    ])>
                        @if ($step['state'] === 'complete')
                            ✓
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    @unless ($loop->last)
                        <span @class([
                            'mt-1 min-h-8 w-0.5 flex-1',
                            'bg-brand' => $step['reached'],
                            'bg-navy/15' => ! $step['reached'],
                        ])></span>
                    @endunless
                </span>
                <div class="min-w-0 pt-0.5">
                    <p @class([
                        'text-sm font-semibold',
                        'text-navy' => $step['state'] !== 'upcoming',
                        'text-navy/45' => $step['state'] === 'upcoming',
                    ])>{{ $step['label'] }}</p>
                    @if ($step['state'] === 'complete')
                        <p class="mt-0.5 text-xs text-navy/55">{{ $step['description'] }}</p>
                        @if ($step['timestamp'])
                            <time class="mt-1 block text-xs text-navy/55" datetime="{{ $step['timestamp']->toIso8601String() }}">{{ $step['timestamp']->format('M j, Y · g:i A') }}</time>
                        @else
                            <p class="mt-1 text-xs text-navy/50">Time not recorded</p>
                        @endif
                    @elseif ($step['state'] === 'current')
                        <p class="mt-0.5 text-xs text-navy/60">{{ $step['waiting'] }}</p>
                    @else
                        <p class="mt-0.5 text-xs text-navy/45">Upcoming</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
