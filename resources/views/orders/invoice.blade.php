<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --navy: #152029;
            --muted: #5b6770;
            --line: #e3e8ee;
            --canvas: #f4f7fa;
            --brand: #ff4917;
            --brand-dark: #d63a10;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 40px 20px 56px;
            background: var(--canvas);
            color: var(--navy);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        .sheet {
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 24px 48px -28px rgba(21, 32, 41, 0.35);
        }
        .accent {
            height: 6px;
            background: linear-gradient(90deg, #ff6a3d, var(--brand-dark));
        }
        .pad { padding: 36px 40px 40px; }
        .top {
            display: flex;
            justify-content: space-between;
            gap: 28px;
            align-items: flex-start;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--line);
        }
        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 10px;
        }
        .brand-mark span {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 10px;
            background: linear-gradient(145deg, #ff6a3d, var(--brand-dark));
            color: #fff;
            font-size: 16px;
            font-weight: 800;
        }
        .brand-mark strong {
            font-size: 18px;
            letter-spacing: -0.02em;
        }
        h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        .invoice-no {
            margin: 8px 0 0;
            color: var(--brand-dark);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .company {
            max-width: 260px;
            text-align: right;
        }
        .company strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            letter-spacing: -0.01em;
        }
        .muted { color: var(--muted); }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin: 28px 0 8px;
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fbfcfd;
            padding: 18px 20px;
        }
        .card h2 {
            margin: 0 0 10px;
            color: var(--brand-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .card p { margin: 0; }
        .card .name {
            margin-bottom: 4px;
            font-size: 16px;
            font-weight: 700;
        }
        table {
            width: 100%;
            margin: 28px 0 8px;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 14px;
        }
        thead th {
            background: var(--navy);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            vertical-align: top;
        }
        th:nth-child(2),
        td:nth-child(2),
        th:last-child,
        td:last-child {
            text-align: right;
            white-space: nowrap;
        }
        tbody td {
            border-top: 1px solid var(--line);
            background: #fff;
        }
        tbody tr:nth-child(even) td { background: #fafbfc; }
        .service { font-weight: 600; }
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .summary-box {
            width: min(100%, 320px);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px 18px;
            background: #fff;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin: 0 0 10px;
            color: var(--muted);
        }
        .summary-row strong { color: var(--navy); }
        .summary-row.total {
            margin: 14px 0 0;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            color: var(--navy);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .payment {
            margin-top: 24px;
            border-left: 4px solid var(--brand);
            border-radius: 0 12px 12px 0;
            background: #fff8f5;
            padding: 16px 18px;
        }
        .payment h2 {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .payment p { margin: 0; color: var(--muted); }
        .footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: 0; border-radius: 0; box-shadow: none; }
        }
        @media (max-width: 640px) {
            .pad { padding: 24px 18px 28px; }
            .top, .meta-grid { grid-template-columns: 1fr; display: grid; }
            .company { text-align: left; max-width: none; }
            th:nth-child(2), td:nth-child(2) { display: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="accent"></div>
        <div class="pad">
            <header class="top">
                <div>
                    <div class="brand-mark">
                        <span aria-hidden="true">B</span>
                        <strong>BacklinkPro</strong>
                    </div>
                    <h1>Invoice</h1>
                    <p class="invoice-no">{{ $invoice->invoice_number }}</p>
                </div>
                <div class="company">
                    <strong>Prime One Global (PVT) LTD.</strong>
                    <p class="muted">
                        #146B, Goodshed Road,<br>
                        Thonikkal, Vavuniya, NP,<br>
                        Sri Lanka.
                    </p>
                    <p class="muted" style="margin-top:12px">Issued {{ $invoice->issued_at->format('M j, Y') }}</p>
                </div>
            </header>

            <div class="meta-grid">
                <section class="card">
                    <h2>Bill to</h2>
                    <p class="name">{{ $invoice->customer->name }}</p>
                    <p class="muted">{{ $invoice->customer->email }}</p>
                </section>
                <section class="card">
                    <h2>Order</h2>
                    <p class="name">{{ $invoice->order->order_number }}</p>
                    <p class="muted">Payment confirmation for this order</p>
                </section>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->order->items as $item)
                        <tr>
                            <td class="service">{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-box">
                    @php
                        $discount = (float) $invoice->order->discount_amount;
                        $subtotal = (float) $invoice->total_amount + $discount;
                    @endphp
                    <p class="summary-row"><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></p>
                    @if ($discount > 0)
                        <p class="summary-row"><span>Discount</span><strong>−${{ number_format($discount, 2) }}</strong></p>
                    @endif
                    <p class="summary-row total"><span>Total</span><span>${{ number_format($invoice->total_amount, 2) }}</span></p>
                </div>
            </div>

            @if (isset($payment))
                <section class="payment">
                    <h2>Payment received</h2>
                    <p>
                        Method: {{ $payment->payment_method }}<br>
            Reference: {{ $payment->transaction_id }}
            @if ($payment->paid_at)
                <br>Paid on: {{ $payment->paid_at->format('M j, Y') }}
            @endif
        </p>
    </section>
@endif

            <p class="footer">Thank you for your business. This invoice was issued by Prime One Global (PVT) LTD.</p>
        </div>
    </div>
</body>
</html>
