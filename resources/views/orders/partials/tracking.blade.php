@php
    $status = (string) $order->order_status;
    $isPaid = $order->payment_status === 'paid';
    $paymentFailed = $order->payment_status === 'failed';

    $completedSteps = match ($status) {
        'awaiting_details', 'awaiting_approval' => 2,
        'processing' => 3,
        'completed' => 5,
        default => $isPaid ? 2 : 1,
    };

    $currentStep = match ($status) {
        'awaiting_details', 'awaiting_approval' => 3,
        'processing' => 4,
        'completed' => 5,
        default => 2,
    };

    $progressPercent = (($completedSteps - 1) / 4) * 100;
    $statusLabel = match ($status) {
        'awaiting_details' => 'Details needed',
        'awaiting_approval' => 'Admin review',
        'processing' => 'In progress',
        'completed' => 'Verified live',
        default => $paymentFailed ? 'Payment failed' : ($isPaid ? str($status)->headline() : 'Payment due'),
    };
    $stageTitle = match (true) {
        $status === 'awaiting_details' => 'One step from review',
        $status === 'awaiting_approval' => 'Your order is under review',
        $status === 'processing' => 'Content and placement are underway',
        $status === 'completed' => 'Your backlink is verified live',
        $paymentFailed => 'Payment needs another try',
        ! $isPaid => 'Complete payment to get started',
        default => 'Your order is moving forward',
    };
    $stageDescription = match (true) {
        $status === 'awaiting_details' => 'Payment is confirmed. Add the target URL and campaign brief below so the team can review your order.',
        $status === 'awaiting_approval' => 'Your publishing details are with the admin team. Content work starts after the review.',
        $status === 'processing' => 'The team is preparing your content and coordinating the backlink placement.',
        $status === 'completed' => 'All placements in this order have passed customer approval.',
        $paymentFailed => 'The payment did not complete. Retry it to continue with your order.',
        ! $isPaid => 'Once payment is confirmed, you can submit your campaign details for review.',
        default => 'We will keep this tracker in step with your order status.',
    };
    $briefDetail = match ($status) {
        'awaiting_details' => 'Details needed',
        'awaiting_approval' => 'With the admin team',
        'processing', 'completed' => 'Review complete',
        default => $isPaid ? 'Ready for review' : 'After payment',
    };
    $placementDetail = match ($status) {
        'processing' => 'In progress',
        'completed' => 'Complete',
        default => 'Next up',
    };
    $stepDetails = [
        ['title' => 'Order received', 'detail' => $order->created_at->format('M j, Y')],
        ['title' => 'Payment cleared', 'detail' => $isPaid ? 'Confirmed' : ($paymentFailed ? 'Needs attention' : 'Awaiting payment')],
        ['title' => 'Brief and review', 'detail' => $briefDetail],
        ['title' => 'Content and placement', 'detail' => $placementDetail],
        ['title' => 'Live and verified', 'detail' => $status === 'completed' ? 'Approved' : 'Final milestone'],
    ];
@endphp

<section class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-navy/10" aria-label="Order tracking">
    <header class="relative isolate overflow-hidden bg-navy px-5 py-6 text-white sm:px-7 sm:py-7">
        <div class="pointer-events-none absolute -top-24 -right-12 size-64 rounded-full border border-white/10" aria-hidden="true">
            <div class="absolute inset-6 rounded-full border border-white/10"></div>
            <div class="absolute inset-14 rounded-full border border-brand/30"></div>
            <span class="absolute top-1/2 left-1/2 size-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand shadow-[0_0_28px_rgba(255,73,23,0.8)]"></span>
        </div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold tracking-[0.18em] text-brand uppercase">Order tracking · {{ $order->order_number }}</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Your campaign, step by step</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/70">Follow your payment, review, content and backlink placement in one clear timeline.</p>
            </div>
            <span @class([
                'inline-flex shrink-0 items-center rounded-full px-3.5 py-2 text-xs font-semibold ring-1 ring-inset',
                'bg-emerald-400/10 text-emerald-100 ring-emerald-200/20' => $status === 'completed',
                'bg-brand/15 text-orange-100 ring-brand/30' => in_array($status, ['awaiting_details', 'processing'], true),
                'bg-amber-300/10 text-amber-100 ring-amber-200/20' => $status === 'awaiting_approval',
                'bg-rose-300/10 text-rose-100 ring-rose-200/20' => $paymentFailed,
                'bg-white/10 text-white/85 ring-white/15' => ! in_array($status, ['completed', 'awaiting_details', 'processing', 'awaiting_approval'], true) && ! $paymentFailed,
            ])>{{ $statusLabel }}</span>
        </div>
    </header>

    <div class="p-5 sm:p-7">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold tracking-[0.16em] text-navy/45 uppercase">Progress overview</p>
                <p class="mt-1 text-sm font-semibold text-navy">{{ $order->items->sum('quantity') }} placement{{ $order->items->sum('quantity') === 1 ? '' : 's' }} · Updated {{ $order->updated_at->format('M j, g:i A') }}</p>
            </div>
            <p class="text-xs font-medium text-navy/50">Step {{ $currentStep }} of 5</p>
        </div>

        <div class="relative mt-7">
            <div class="absolute top-4 bottom-4 left-4 border-l-2 border-dotted border-navy/15 sm:hidden" aria-hidden="true"></div>
            <div class="absolute top-4 right-[10%] left-[10%] hidden h-0.5 bg-navy/10 sm:block" aria-hidden="true">
                <span class="block h-full bg-brand transition-all duration-500" style="width: {{ $progressPercent }}%"></span>
            </div>

            <ol class="relative grid gap-4 sm:grid-cols-5 sm:gap-1" aria-label="Order progress: {{ $statusLabel }}">
                @foreach ($stepDetails as $step)
                    @php
                        $isComplete = $loop->iteration <= $completedSteps;
                        $isCurrent = ! $isComplete && $loop->iteration === $currentStep;
                    @endphp
                    <li @if ($isCurrent) aria-current="step" @endif class="flex items-start gap-3 sm:block sm:text-center">
                        <span @class([
                            'relative z-10 flex size-8 shrink-0 items-center justify-center rounded-full border text-xs font-bold transition sm:mx-auto',
                            'border-brand bg-brand text-white shadow-[0_0_0_5px_rgba(255,73,23,0.12)]' => $isCurrent,
                            'border-brand bg-brand text-white' => $isComplete,
                            'border-navy/15 bg-white text-navy/45' => ! $isComplete && ! $isCurrent,
                        ])>
                            <span aria-hidden="true">{{ $isComplete ? '✓' : $loop->iteration }}</span>
                        </span>
                        <span class="min-w-0 pb-1 sm:mt-3 sm:block sm:px-1">
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

        <div class="mt-7 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-brand/15 bg-[#fff5f0] p-4 sm:p-5">
            <div class="min-w-0 flex-1 basis-64">
                <p class="text-[10px] font-semibold tracking-[0.16em] text-brand uppercase">Current step</p>
                <h3 class="mt-1 text-base font-semibold text-navy">{{ $stageTitle }}</h3>
                <p class="mt-1 text-sm leading-6 text-navy/65">{{ $stageDescription }}</p>
            </div>
            @if ($status === 'awaiting_details')
                <a href="#publishing-details" class="inline-flex min-h-11 items-center justify-center rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Add campaign details</a>
            @elseif (! $isPaid)
                <a href="{{ route('orders.payment', $order) }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">{{ $paymentFailed ? 'Retry payment' : 'Complete payment' }}</a>
            @endif
        </div>
    </div>
</section>
