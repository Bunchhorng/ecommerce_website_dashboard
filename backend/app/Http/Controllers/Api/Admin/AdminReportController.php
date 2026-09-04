<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminReportController extends Controller
{
    protected function applyFilters(Request $request, $query)
    {
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('placed_at', '>=', Carbon::parse($request->from)->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('placed_at', '<=', Carbon::parse($request->to)->endOfDay());
        }

        return $query;
    }

    public function ordersCsv(Request $request)
    {
        $query = $this->applyFilters($request, Order::query()->orderByDesc('placed_at'));

        $orders = $query->get();

        $headers = [
            'order_number', 'placed_at', 'customer_name', 'email', 'status',
            'payment_status', 'subtotal', 'discount_amount', 'tax_amount',
            'shipping_amount', 'total', 'coupon_code',
        ];

        return response()->streamDownload(function () use ($orders, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->placed_at?->toDateTimeString(),
                    $order->customer_name,
                    $order->email,
                    $order->status,
                    $order->payment_status,
                    (string) $order->subtotal,
                    (string) $order->discount_amount,
                    (string) $order->tax_amount,
                    (string) $order->shipping_amount,
                    (string) $order->total,
                    $order->coupon_code,
                ]);
            }

            fclose($handle);
        }, 'orders-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function ordersPdf(Request $request)
    {
        $query = $this->applyFilters($request, Order::query()->orderByDesc('placed_at'));

        $orders = $query->get();

        $pdf = Pdf::loadView('reports.orders', [
            'orders' => $orders,
            'status' => $request->filled('status') ? $request->status : 'all',
        ]);

        return $pdf->download('orders-' . now()->format('Y-m-d') . '.pdf');
    }
}
