<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $invoice->invoice_number }}</title><style>body{font-family:Arial,sans-serif;color:#152029;max-width:760px;margin:48px auto;padding:0 24px}header,.row{display:flex;justify-content:space-between;gap:24px}header{border-bottom:3px solid #ff4917;padding-bottom:24px}.muted{color:#65717a}.total{font-size:24px;font-weight:700;border-top:1px solid #ccd1d4;padding-top:20px;text-align:right}table{width:100%;border-collapse:collapse;margin:32px 0}th,td{text-align:left;padding:14px;border-bottom:1px solid #e1e4e6}th:last-child,td:last-child{text-align:right}</style></head>
<body><header><div><h1>Invoice</h1><p>{{ $invoice->invoice_number }}</p></div><div><strong>Backlink Management</strong><p class="muted">Issued {{ $invoice->issued_at->format('M j, Y') }}</p></div></header><section><p><strong>Bill to</strong></p><p>{{ $invoice->customer->name }}<br>{{ $invoice->customer->email }}</p><p class="muted">Order {{ $invoice->order->order_number }}</p></section><table><thead><tr><th>Service</th><th>Qty</th><th>Amount</th></tr></thead><tbody>@foreach ($invoice->order->items as $item)<tr><td>{{ $item->item_name }}</td><td>{{ $item->quantity }}</td><td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td></tr>@endforeach</tbody></table>@if (isset($payment))
    <section>
        <p><strong>Payment received</strong></p>
        <p>Method: {{ $payment->payment_method }}<br>
            Reference: {{ $payment->transaction_id }}
            @if ($payment->paid_at)
                <br>Paid on: {{ $payment->paid_at->format('M j, Y') }}
            @endif
        </p>
    </section>
@endif
@if ((float) $invoice->order->discount_amount > 0)
    <p>Discount: −${{ number_format($invoice->order->discount_amount, 2) }}</p>
@endif
<p class="total">Total ${{ number_format($invoice->total_amount, 2) }}</p></body>
</html>