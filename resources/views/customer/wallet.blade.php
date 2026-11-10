@extends('layouts.customer')

@section('title', 'Wallet')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">My account</p>
                <h1 class="mt-2 text-3xl font-semibold">Credit wallet</h1>
                <p class="mt-2 text-sm text-navy/60">Credits are reserved at submission, charged after verified publication, and released when publishing fails.</p>
            </div>
            <a class="text-sm font-semibold text-brand hover:underline" href="{{ route('customer.payment-methods') }}">Payment methods</a>
        </div>
        @if (session('success'))
            <div class="rounded-xl bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <section class="panel p-6"><p class="text-sm text-navy/55">Available credits</p><p class="mt-2 text-4xl font-semibold">{{ number_format($wallet->available_balance) }}</p></section>
            <section class="panel p-6"><p class="text-sm text-navy/55">Reserved credits</p><p class="mt-2 text-4xl font-semibold">{{ number_format($wallet->reserved_balance) }}</p></section>
        </div>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Credit packages</h2>
            <div class="grid gap-4 md:grid-cols-3">
                @forelse ($packages as $package)
                    <article class="panel p-5">
                        <div class="flex justify-between gap-3"><h3 class="font-semibold">{{ $package->name }}</h3><span class="font-display text-xl font-semibold">{{ number_format($package->credits) }}</span></div>
                        <p class="mt-1 text-xs text-navy/55">credits</p>
                        <p class="mt-4 text-2xl font-semibold">${{ number_format($package->price, 2) }}</p>
                        <form method="POST" action="{{ route('customer.wallet.purchase', $package) }}" class="mt-4">
                            @csrf
                            <button class="btn-primary w-full" type="submit">Buy credits</button>
                        </form>
                    </article>
                @empty
                    <p class="text-sm text-navy/55">No active credit packages.</p>
                @endforelse
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4"><h2 class="font-semibold">Credit transactions</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-navy text-white"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Activity</th><th class="px-5 py-3">Credits</th></tr></thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr class="border-b border-navy/10">
                                <td class="px-5 py-3">{{ $transaction->created_at->format('M j, Y') }}</td>
                                <td class="px-5 py-3">{{ $transaction->description }}</td>
                                <td class="px-5 py-3 font-semibold {{ in_array($transaction->transaction_type, ['purchase', 'refund', 'release']) ? 'text-green-700' : 'text-navy' }}">{{ in_array($transaction->transaction_type, ['purchase', 'refund', 'release']) ? '+' : '-' }}{{ number_format($transaction->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-navy/55">No credit activity yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $transactions->links() }}</div>
        </section>
    </div>
@endsection