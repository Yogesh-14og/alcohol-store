<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }} - Khanal Liquor</title>
    <style>
        @page {
            size: 80mm auto;          /* 80mm width, height auto */
            margin: 35px 35px 35px 35px;  /* top, right, bottom, left margins */
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 3mm 2mm;         
            width: 65mm;             
            font-family: 'DejaVu Sans Mono', 'Consolas', 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.35;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .large { font-size: 13px; }
        .small { font-size: 8.5px; }
        hr { border: 0; border-top: 1px dashed #000; margin: 2mm 0; }
        .dashed { border-top: 1px dashed #000; padding-top: 1.5mm; margin-top: 1.5mm; }
        table { width: 100%; border-collapse: collapse; margin: 1.5mm 0; }
        th, td { padding: 1px 0; vertical-align: top; font-size: 9.5px; }
        th { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 1mm; }
        .col-item { width: 55%; }
        .col-qty { width: 12%; text-align: center; }
        .col-price { width: 15%; text-align: right; }
        .col-total { width: 18%; text-align: right; }
        .totals-table td { padding: 0.5px 0; }
        .totals-table .bold { font-weight: bold; }
        .no-print { display: none; }
        @media print {
            body { padding: 2mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Print Button (Hidden on Print) -->
    <div class="no-print" style="text-align:center; margin-bottom:2mm; padding:1mm;">
        <button onclick="window.print()" style="padding:4px 10px; font-size:11px; background:#202124; color:#fff; border:none; border-radius:3px; cursor:pointer;">
            Print Receipt
        </button>
        <a href="{{ route('admin.orders.show', $order) }}" style="margin-left:8px; font-size:11px; color:#202124; text-decoration:underline;">Back</a>
    </div>

    <main>
        <!-- Header -->
        <div class="text-center">
            <div class="bold large">Khanal Liquor</div>
            <div class="small">{{ config('company.address', 'Kathmandu, Nepal') }}</div>
            <div class="small">{{ config('company.phone', '9745390311') }}</div>
        </div>
        <hr>

        <!-- Invoice Meta -->
        <div class="small">
            <div><span class="bold">Invoice:</span> {{ $order->order_number }}</div>
            <div><span class="bold">Date:</span> {{ $order->created_at->format('d M Y H:i') }}</div>
            <div><span class="bold">Status:</span> {{ ucfirst($order->status ?? 'pending') }}</div>
            <div><span class="bold">Cashier:</span> {{ $order->user->name ?? 'N/A' }}</div>
        </div>
        <hr>

        <!-- Customer -->
        @if($order->shipping_name || $order->shipping_phone || $order->shipping_address)
        <div class="small bold">Customer</div>
        <div class="small">
            @if($order->shipping_name){{ $order->shipping_name }}<br>@endif
            @if($order->shipping_phone){{ $order->shipping_phone }}<br>@endif
            @if($order->shipping_address){{ $order->shipping_address }}@endif
        </div>
        <hr>
        @endif

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th class="col-item">ITEM</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-price">RATE</th>
                    <th class="col-total">AMT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td class="col-item">
                            {{ $item->product_name }}
                            @if(!empty($item->variant))
                                <br><span class="small">  {{ $item->variant }}</span>
                            @endif
                        </td>
                        <td class="col-qty">{{ $item->quantity }}</td>
                        <td class="col-price">NPR {{ number_format((float)$item->unit_price, 2) }}</td>
                        <td class="col-total">NPR {{ number_format((float)$item->unit_price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td class="text-left"><Total</td>
                <td class="text-right">NPR {{ number_format((float)$order->total_amount, 2) }}</td>
            </tr>
            @if(($order->discount_amount ?? 0) > 0)
            <tr>
                <td class="text-left">Discount</td>
                <td class="text-right">- NPR {{ number_format((float)$order->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if(($order->tax_amount ?? 0) > 0)
            <tr>
                <td class="text-left">Tax ({{ $order->tax_percent ?? 0 }}%)</td>
                <td class="text-right">NPR {{ number_format((float)$order->tax_amount, 2) }}</td>
            </tr>
            @endif
        </table>
        <hr>

        <!-- Payment Info -->
        <div class="small">
            <div><span class="bold">Payment:</span> {{ strtoupper($order->payment_method ?? 'COD') }}</div>
            <div><span class="bold">Status:</span> 
                {{ $order->balance_due_cents === 0 ? 'Paid' : (($order->received_amount_cents ?? 0) > 0 ? 'Partial' : 'Unpaid') }}
            </div>
            <div><span class="bold">Received:</span> NPR {{ number_format(($order->received_amount_cents ?? 0) / 100, 2) }}</div>
            <div><span class="bold">Balance:</span> NPR {{ number_format(($order->balance_due_cents ?? 0) / 100, 2) }}</div>
        </div>
        <hr>

        <!-- Footer -->
        <div class="text-center small">
            <div>Thank you for shopping!</div>
            <div>Visit again</div>
            <div class="dashed text-center">
                <span class="small">Powered by Khanal Liquor System</span>
            </div>
        </div>
    </main>

    <script>
        // Auto-print on load
        window.addEventListener('load', () => {
            window.print();
        });

        // Optional: Return to order after print (browser dependent)
        window.addEventListener('afterprint', () => {
            // window.location.assign({{ json_encode(route('admin.orders.show', $order)) }});
        });
    </script>
</body>
</html>