<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 0; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #6b7280; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 16px; }
        .meta { display: flex; justify-content: space-between; font-size: 10px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; padding: 6px 4px; }
        td { padding: 6px 4px; border-bottom: 1px solid #f3f4f6; }
        tr:nth-child(even) td { background: #f9fafb; }
        .right { text-align: right; }
        .total-row td { border-top: 2px solid #111827; font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Orders Report</h1>
        <p class="muted">ShopVerse</p>
    </div>
    <div class="meta">
        <span>Generated: {{ now()->format('Y-m-d H:i') }}</span>
        <span>{{ $orders->count() }} order(s)</span>
        <span>Status: {{ ucfirst($status) }}</span>
    </div>
    <br>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Placed</th>
                <th>Customer</th>
                <th>Status</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->placed_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $order->customer_name }}<br><span class="muted">{{ $order->email }}</span></td>
                    <td>{{ ucfirst($order->status) }} / {{ ucfirst($order->payment_status) }}</td>
                    <td class="right">${{ number_format((float) $order->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No orders found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4">Total</td>
                <td class="right">${{ number_format((float) $orders->sum('total'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <div class="footer">ShopVerse · Orders Report · Generated {{ now()->format('Y-m-d H:i') }}</div>
</body>
</html>
