<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }} - Khanal Liquor</title>
    <style>
        body { color: #202124; font: 15px/1.5 Arial, sans-serif; margin: 0; }
        .invoice { margin: 40px auto; max-width: 800px; padding: 32px; }
        .header, .row, .total { align-items: start; display: flex; justify-content: space-between; gap: 24px; }
        .header { border-bottom: 2px solid #202124; margin-bottom: 28px; padding-bottom: 20px; }
        h1, h2, p { margin: 0; }
        h1 { font-size: 28px; }
        h2 { font-size: 17px; margin-bottom: 10px; }
        .muted { color: #62666a; }
        .details { display: grid; gap: 24px; grid-template-columns: 1fr 1fr; margin-bottom: 28px; }
        table { border-collapse: collapse; margin: 16px 0; width: 100%; }
        th, td { border-bottom: 1px solid #d7d9dc; padding: 11px 8px; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .total { border-top: 2px solid #202124; font-size: 18px; font-weight: 700; padding-top: 14px; }
        .actions { margin: 0 auto 24px; max-width: 800px; padding: 0 32px; text-align: right; }
        button { background: #202124; border: 0; border-radius: 5px; color: #fff; cursor: pointer; padding: 10px 18px; }
        @media print {
            .actions { display: none; }
            .invoice { margin: 0 auto; padding: 12mm 10mm; }
            @page { margin: 12mm; }
        }
        @media (max-width: 600px) {
            .invoice { margin: 12px auto; padding: 20px; }
            .details { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="{{ route('admin.orders.show', $order) }}" style="margin-right: 12px;">Back to order</a>
        <button type="button" onclick="window.print()">Print invoice</button>
    </div>
    <main class="invoice">
        <header class="header">
            <div>
                <h1>Khanal Liquor</h1>
                <p class="muted">Order invoice</p>
            </div>
            <div style="text-align: right;">
                <p><strong>Invoice:</strong> {{ $order->order_number }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('M j, Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            </div>
        </header>

        <section class="details">
            <div>
                <h2>Deliver to</h2>
                <p>{{ $order->shipping_name ?? $order->user?->name ?? 'Customer' }}</p>
                @if ($order->shipping_phone)<p>{{ $order->shipping_phone }}</p>@endif
                @if ($order->shipping_address)<p>{{ $order->shipping_address }}</p>@endif
                @if (! $order->shipping_name && ! $order->shipping_address && $order->user)
                    <p>{{ $order->user->email }}</p>
                @endif
            </div>
            <div>
                <h2>Payment</h2>
                <p>{{ strtoupper($order->payment_method ?? 'COD') }}</p>
                <p class="muted">Payment status: {{ $order->balance_due_cents === 0 ? 'Paid' : ($order->received_amount_cents > 0 ? 'Partially paid' : 'Unpaid') }}</p>
                <p class="muted">Received: NPR {{ number_format($order->received_amount_cents / 100, 2) }}</p>
                <p class="muted">Balance due: NPR {{ number_format($order->balance_due_cents / 100, 2) }}</p>
            </div>
        </section>

        <table>
            <thead>
                <tr><th>Item</th><th>Unit price</th><th>Qty</th><th>Amount</th></tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>NPR {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>NPR {{ number_format((float) $item->unit_price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            <span>Total</span>
            <span>NPR {{ number_format((float) $order->total_amount, 2) }}</span>
        </div>
        <p class="muted" style="margin-top: 28px;">Thank you for shopping with Khanal Liquor.</p>
    </main>
    <script>
        window.addEventListener('afterprint', () => {
            window.location.assign(@json(route('admin.orders.show', $order)));
        });

        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
