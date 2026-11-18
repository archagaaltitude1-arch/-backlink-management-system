@extends('layouts.public')

@php
    $limit = $limit ?? null;
@endphp

@section('document_title', 'Select Backlink Websites | BacklinkPro')
@section('meta_description', $lead)

@section('content')
    <style>
        tr.is-frozen { opacity: 0.45; }
        tr.is-frozen .nd-pick,
        [data-site-choice]:disabled { cursor: not-allowed; }
    </style>
    @if (session('shopping_error'))
        <p class="pub-status" role="alert" style="max-width:none;margin:0 auto;padding:28px 20px 0">{{ session('shopping_error') }}</p>
    @endif
    @if (session('success'))
        <p class="pub-status" role="status" style="max-width:none;margin:0 auto;padding:28px 20px 0">{{ session('success') }}</p>
    @endif

    <form id="site-selection" method="POST" action="{{ ! empty($orderId) ? route('packages.websites.assign', $package['slug']) : route('shopping.store') }}" data-site-selection data-limit="{{ $limit ?? 0 }}">
        @csrf
        <input type="hidden" name="package_slug" value="{{ $package['slug'] }}">
        <input type="hidden" name="package_id" value="{{ $package['package_id'] }}">
        <input type="hidden" name="quantity" value="{{ $quantity }}">
        <input type="hidden" name="order_type" value="{{ $orderType }}">
        @if (! empty($backlinks))
            <input type="hidden" name="backlinks" value="{{ $backlinks }}">
        @endif
        @if (! empty($orderId))
            <input type="hidden" name="order" value="{{ $orderId }}">
        @elseif (! empty($backlinks))
            <input type="hidden" name="website_selection" value="1">
        @endif
    </form>

    @include('customer.publishers.listing', [
        'rows' => $rows,
        'demo' => $demo,
        'categories' => $categories,
        'title' => 'Select Backlink Websites',
        'lead' => $lead,
        'breadcrumb' => $choice,
        'showProject' => false,
        'selectable' => true,
        'selectionForm' => 'site-selection',
        'selectionLimit' => $limit,
        'selectionButton' => empty($orderId) && ! empty($backlinks) ? 'Continue to Checkout' : null,
    ])
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('[data-site-selection]');
    if (!form) return;
    var limit = Number(form.getAttribute('data-limit') || 0);
    var boxes = Array.prototype.slice.call(document.querySelectorAll('[data-site-choice]'));
    var status = document.getElementById('selection-count');
    var button = document.querySelector('[data-selection-submit]');
    var paint = function () {
        var chosen = boxes.filter(function (box) { return box.checked; });
        boxes.forEach(function (box) {
            var frozen = limit > 0 && !box.checked && chosen.length >= limit;
            box.disabled = frozen;
            var row = box.closest('tr');
            if (row) row.classList.toggle('is-frozen', frozen);
        });
        if (status) {
            status.textContent = limit > 0
                ? chosen.length + '/' + limit
                : chosen.length + (chosen.length === 1 ? ' website selected' : ' websites selected');
        }
        if (button) {
            button.disabled = limit > 0 ? chosen.length !== limit : chosen.length < 1;
        }
    };
    boxes.forEach(function (box) { box.addEventListener('change', paint); });
    paint();
})();
</script>
@endpush
