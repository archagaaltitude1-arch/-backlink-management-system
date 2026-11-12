@extends('layouts.admin')

@section('title', 'SEO Staff Performance')

@section('content')
    <div class="space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">SEO Staff Performance</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-navy/70">Assignment records for each SEO staff member during {{ $period->label() }}. Pending means assigned and not started. Awaiting approval means the backlink is published for the customer.</p>
        </section>
        @include('admin.partials.period-filter', ['action' => route('admin.staff-performance'), 'period' => $period])
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Completed assignments</h2>
            <div class="mt-6">
                @include('admin.partials.bar-chart', ['chart' => $staff->map(fn ($member) => ['label' => strtok($member['name'], ' ') ?: $member['name'], 'value' => $member['completed'], 'display' => (string) $member['completed']])->all()])
            </div>
        </section>
        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[860px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Staff</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Assigned</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Pending</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">In progress</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Awaiting approval</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Completed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($staff as $member)
                            <tr>
                                <td class="px-5 py-4 sm:px-6">
                                    <a href="{{ route('admin.staff.show', $member['id']) }}" class="font-medium hover:text-brand">{{ $member['name'] }}</a>
                                    <p class="mt-1 text-xs text-navy/55">{{ $member['role'] }} · {{ ucfirst($member['status']) }}</p>
                                    <p class="mt-1 text-xs text-navy/60">Completed {{ $member['completed'] }} of {{ $member['assigned'] }} assignments in this period.</p>
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums">{{ $member['assigned'] }}</td>
                                <td class="px-4 py-4 text-right tabular-nums">{{ $member['pending'] }}</td>
                                <td class="px-4 py-4 text-right tabular-nums">{{ $member['progress'] }}</td>
                                <td class="px-4 py-4 text-right tabular-nums">{{ $member['review'] }}</td>
                                <td class="px-5 py-4 text-right font-medium tabular-nums sm:px-6">{{ $member['completed'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No SEO staff accounts are stored yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
