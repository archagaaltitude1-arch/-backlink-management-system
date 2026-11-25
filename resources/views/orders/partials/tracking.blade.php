@php
    $status = (string) $order->order_status;
    $isPaid = $order->payment_status === 'paid';
    $paymentFailed = $order->payment_status === 'failed';
    $isAccepted = in_array($status, ['processing', 'completed'], true);
    $isDelivered = $status === 'completed';
    $isInProgress = $status === 'completed' || $order->items->contains(fn ($item): bool => in_array($item->assignment?->status, ['in_progress', 'published', 'completed'], true));
    $isOnTheWay = $isDelivered || $order->items->contains(fn ($item): bool => filled($item->project?->published_url)
        || in_array($item->assignment?->status, ['published', 'completed'], true));
    $hasTeamAssignment = $order->items->contains(fn ($item): bool => $item->assignment !== null);

    $startedAt = $order->items
        ->map(fn ($item) => $item->assignment?->status === 'in_progress' ? $item->assignment->updated_at : null)
        ->filter()
        ->sort()
        ->first();
    $publishedAt = $order->items
        ->map(fn ($item) => $item->article?->published_at)
        ->filter()
        ->sort()
        ->first();
    $deliveredAt = $order->items
        ->map(fn ($item) => $item->project?->completed_at)
        ->filter()
        ->sortDesc()
        ->first();

    $completedSteps = match (true) {
        $isDelivered => 5,
        $isOnTheWay => 4,
        $isInProgress => 3,
        $isAccepted => 2,
        default => 1,
    };
    $currentStep = min($completedSteps + 1, 5);
    $progressPercent = (($completedSteps - 1) / 4) * 100;

    $statusLabel = match (true) {
        $paymentFailed => 'Payment needs attention',
        ! $isPaid => 'Awaiting payment',
        $status === 'awaiting_details' => 'Campaign details needed',
        $status === 'awaiting_approval' => 'Awaiting admin approval',
        $isDelivered => 'Delivered',
        $isOnTheWay => 'Placement published',
        $isInProgress => 'In progress',
        $isAccepted => 'Accepted',
        default => 'Awaiting approval',
    };

    $currentTitle = match (true) {
        $paymentFailed => 'Payment needs another try',
        ! $isPaid => 'Complete payment to confirm your order',
        $status === 'awaiting_details' => 'Add your campaign details',
        $status === 'awaiting_approval' => 'Your order is waiting for approval',
        $isDelivered => 'Your order has been delivered',
        $isOnTheWay => 'Your placement is ready for review',
        $isInProgress => 'The team is working on your placement',
        $isAccepted => $hasTeamAssignment ? 'Your order is with the SEO team' : 'Your order is approved and getting started',
        default => 'Your order is waiting for approval',
    };

    $currentDescription = match (true) {
        $paymentFailed => 'Retry payment to move your order forward.',
        ! $isPaid => 'Once payment clears, the team can accept and prepare your order.',
        $status === 'awaiting_details' => 'Share the target URL and brief so the admin team can review the order.',
        $status === 'awaiting_approval' => 'Payment is confirmed. Work begins after the admin team approves the order.',
        $isDelivered => 'Your backlink has passed review and the order is complete.',
        $isOnTheWay => 'The placement has been published. Review the live link in your order details.',
        $isInProgress => 'Your campaign is approved and moving through content and placement.',
        $isAccepted => $hasTeamAssignment ? 'Your SEO team is assigned and will begin content work next.' : 'The admin team approved your order. The SEO team assignment is next.',
        default => 'Your order is confirmed and will move into fulfillment next.',
    };

    $stepDetails = [
        ['title' => 'Order placed', 'detail' => $order->created_at->format('M j · g:i A')],
        ['title' => 'Accepted', 'detail' => $isAccepted ? 'Approved by the team' : ($paymentFailed ? 'Payment failed' : ($status === 'awaiting_details' ? 'Campaign details needed' : ($isPaid ? 'Awaiting approval' : 'Awaiting payment')))],
        ['title' => 'In progress', 'detail' => $isInProgress ? ($startedAt?->format('M j · g:i A') ?? 'Content work started') : ($status === 'awaiting_approval' ? 'After admin approval' : ($status === 'awaiting_details' ? 'After campaign details' : ($isAccepted ? ($hasTeamAssignment ? 'Assigned · work starts next' : 'Awaiting team assignment') : 'After acceptance')))],
        ['title' => 'On the way', 'detail' => $isOnTheWay ? ($publishedAt?->format('M j · g:i A') ?? 'Published') : ($isInProgress ? 'Preparing placement' : 'After content review')],
        ['title' => 'Delivered', 'detail' => $isDelivered ? ($deliveredAt?->format('M j · g:i A') ?? 'Order complete') : 'After customer approval'],
    ];
@endphp

<section class="mx-auto max-w-6xl overflow-hidden rounded-3xl border border-navy/10 bg-white shadow-sm" aria-label="Order tracking">
    <header class="flex flex-wrap items-start justify-between gap-4 border-b border-navy/10 px-5 py-5 sm:px-7">
        <div>
            <p class="text-[10px] font-bold tracking-[0.18em] text-brand uppercase">Your order journey</p>
            <h2 class="mt-1 text-xl font-semibold tracking-tight text-navy sm:text-2xl">Track your placement</h2>
            <p class="mt-1 text-sm text-navy/55">Follow each milestone from checkout to a verified live link.</p>
        </div>
        <span @class([
            'inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold',
            'border-emerald-200 bg-emerald-50 text-emerald-800' => $isDelivered,
            'border-brand/20 bg-[#fff3ed] text-[#c0340d]' => ! $isDelivered && ! $paymentFailed,
            'border-rose-200 bg-rose-50 text-rose-800' => $paymentFailed,
        ])>{{ $statusLabel }}</span>
    </header>

    <div class="p-5 sm:p-7">
        <div class="relative">
            <div class="absolute top-5 right-[10%] left-[10%] hidden h-0.5 bg-navy/10 sm:block" aria-hidden="true">
                <span class="block h-full bg-brand transition-all duration-500" style="width: {{ $progressPercent }}%"></span>
            </div>
            <div class="absolute top-5 bottom-5 left-5 border-l-2 border-dotted border-navy/15 sm:hidden" aria-hidden="true"></div>

            <ol class="relative grid gap-5 sm:grid-cols-5 sm:gap-1" aria-label="Order progress: {{ $statusLabel }}">
                @foreach ($stepDetails as $step)
                    @php
                        $isComplete = $loop->iteration <= $completedSteps;
                        $isCurrent = ! $isComplete && $loop->iteration === $currentStep;
                    @endphp
                    <li @if ($isCurrent) aria-current="step" @endif class="flex items-start gap-3 sm:block sm:px-1 sm:text-center">
                        <span @class([
                            'relative z-10 flex size-10 shrink-0 items-center justify-center rounded-full border transition sm:mx-auto',
                            'border-brand bg-brand text-white shadow-[0_0_0_5px_rgba(255,73,23,0.12)]' => $isCurrent,
                            'border-brand bg-brand text-white' => $isComplete,
                            'border-navy/15 bg-white text-navy/40' => ! $isComplete && ! $isCurrent,
                        ]) aria-hidden="true">
                            @switch($loop->iteration)
                                @case(1)
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7.5 12 4l7 3.5v9L12 20l-7-3.5z"/><path d="m5.5 7.8 6.5 3.4 6.5-3.4M12 11.2V20"/></svg>
                                    @break
                                @case(2)
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5 19 6v5.2c0 4.3-2.8 7.7-7 9.3-4.2-1.6-7-5-7-9.3V6z"/><path d="m8.5 11.8 2.2 2.2 4.8-4.8"/></svg>
                                    @break
                                @case(3)
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3.5 8 4.3v8.4l-8 4.3-8-4.3V7.8z"/><path d="m4.4 8 7.6 4 7.6-4M12 12v8.2"/></svg>
                                    @break
                                @case(4)
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6.5h11v10H3zM14 10h4l3 3v3.5h-7z"/><circle cx="7" cy="18" r="1.8"/><circle cx="18" cy="18" r="1.8"/><path d="M16.5 10v3h4"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8 12 4l7 4v8l-7 4-7-4z"/><path d="m5.5 8.3 6.5 3.5 6.5-3.5M12 11.8V20"/><path d="m9.4 15.2 1.7 1.7 3.7-3.7"/></svg>
                            @endswitch
                        </span>
                        <span class="min-w-0 pb-1 sm:mt-3 sm:block">
                            <span @class([
                                'block text-sm font-semibold',
                                'text-navy' => $isComplete || $isCurrent,
                                'text-navy/45' => ! $isComplete && ! $isCurrent,
                            ])>{{ $step['title'] }}</span>
                            <span class="mt-1 block text-xs leading-5 text-navy/50">{{ $step['detail'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-brand/15 bg-[#fff5f0] p-4 sm:p-5">
            <div class="min-w-0 flex-1 basis-64">
                <p class="text-[10px] font-bold tracking-[0.16em] text-brand uppercase">Latest update</p>
                <h3 class="mt-1 text-base font-semibold text-navy">{{ $currentTitle }}</h3>
                <p class="mt-1 text-sm leading-6 text-navy/65">{{ $currentDescription }}</p>
            </div>
            @if ($status === 'awaiting_details')
                <a href="#publishing-details" class="inline-flex min-h-11 items-center justify-center rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Add campaign details</a>
            @elseif (! $isPaid)
                <a href="{{ route('orders.payment', $order) }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">{{ $paymentFailed ? 'Retry payment' : 'Complete payment' }}</a>
            @endif
        </div>
    </div>
</section>
