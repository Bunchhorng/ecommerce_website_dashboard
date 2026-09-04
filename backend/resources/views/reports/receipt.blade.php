<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 0; }
        h1 { font-size: 16px; margin: 0; }
        .muted { color: #6b7280; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 16px; }
        .brand { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
        .receipt-meta { font-size: 10px; color: #6b7280; margin-top: 4px; }
        .grid2 { display: flex; justify-content: space-between; gap: 24px; }
        .col-title { font-size: 10px; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; padding: 6px 4px; }
        td { padding: 6px 4px; border-bottom: 1px solid #f3f4f6; }
        tr:nth-child(even) td { background: #f9fafb; }
        .right { text-align: right; }
        .totals { margin-top: 16px; margin-left: auto; width: 55%; }
        .totals div { display: flex; justify-content: space-between; padding: 3px 4px; font-size: 11px; }
        .totals .grand { border-top: 2px solid #111827; font-weight: bold; font-size: 13px; padding-top: 6px; }
        .status-paid { display: inline-block; font-size: 10px; font-weight: bold; padding: 2px 10px; border-radius: 10px; background: #d1fae5; color: #065f46; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">SHOPVERSE</div>
            <p class="muted">E-Commerce Store</p>
        </div>
        <div class="right">
            <h1>Receipt</h1>
            <div class="receipt-meta">
                <div>Order: {{ $order->order_number }}</div>
                <div>Date: {{ $order->placed_at?->format('Y-m-d H:i') }}</div>
                @if($order->payment)
                    <div>Transaction: {{ $order->payment->transaction_id ?? '—' }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid2">
        <div>
            <div class="col-title">Billed To</div>
            <div>
                <div>{{ $order->customer_name }}</div>
                <div>{{ $order->email }}</div>
                @if($order->phone)<div>{{ $order->phone }}</div>@endif
                @php $billing = $order->billing_address ?: $order->shipping_address; @endphp
                @if(is_array($billing))
                    <div>{{ $billing['address_line1'] ?? '' }}</div>
                    <div>{{ trim(($billing['city'] ?? '') . ', ' . ($billing['state'] ?? '') . ' ' . ($billing['postal_code'] ?? '')) }}</div>
                    <div>{{ $billing['country'] ?? '' }}</div>
                @endif
            </div>
        </div>
        <div class="right">
            <div class="col-title">Payment Status</div>
            @if($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                <span class="status-paid">PAID</span>
            @else
                <span class="muted">{{ ucfirst($order->payment_status) }}</span>
            @endif
            @if($order->payment)
                <div class="muted" style="margin-top:6px">
                    <div>Method: {{ ucfirst($order->payment->method) }}</div>
                    @if($order->payment->paid_at)
                        <div>Paid: {{ $order->payment->paid_at->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>SKU</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}@if($item->variant_label)<br><span class="muted">{{ $item->variant_label }}</span>@endif</td>
                    <td>{{ $item->sku }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">${{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span>${{ number_format((float) $order->subtotal, 2) }}</span></div>
        @if((float) $order->discount_amount > 0)
            <div><span>Discount</span><span>-${{ number_format((float) $order->discount_amount, 2) }}</span></div>
        @endif
        <div><span>Shipping</span><span>${{ number_format((float) $order->shipping_amount, 2) }}</span></div>
        @if((float) $order->tax_amount > 0)
            <div><span>Tax</span><span>${{ number_format((float) $order->tax_amount, 2) }}</span></div>
        @endif
        <div class="grand"><span>Total</span><span>${{ number_format((float) $order->total, 2) }}</span></div>
    </div>

    <div class="footer">Thank you for shopping with ShopVerse · {{ $order->order_number }}</div>
</body>
</html>
