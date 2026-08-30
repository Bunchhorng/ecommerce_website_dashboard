<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function metrics(): array
    {
        $totalRevenue = (float) Order::where('payment_status', Order::PAYMENT_PAID)->sum('total');
        $ordersCount = Order::count();
        $customersCount = User::where('role', User::ROLE_CUSTOMER)->count();
        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();
        $lowStock = Inventory::query()
            ->whereRaw('quantity - reserved_quantity <= low_stock_threshold')
            ->count();

        return [
            'total_revenue' => round($totalRevenue, 2),
            'orders_count' => $ordersCount,
            'customers_count' => $customersCount,
            'pending_orders' => $pendingOrders,
            'low_stock_products' => $lowStock,
        ];
    }

    public function revenueTrend(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1)->startOfDay();

        $rows = Order::where('payment_status', Order::PAYMENT_PAID)
            ->where('placed_at', '>=', $start)
            ->selectRaw('DATE(placed_at) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $series[] = [
                'date' => $key,
                'revenue' => round((float) ($rows[$key] ?? 0), 2),
            ];
        }

        return $series;
    }

    public function orderStatusDistribution(): array
    {
        return Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->map(fn ($count, $status) => [
                'status' => $status,
                'count' => (int) $count,
            ])
            ->values()
            ->all();
    }

    public function salesByCategory(): array
    {
        return DB::table('categories')
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->where('categories.is_active', true)
            ->selectRaw('categories.id, categories.name, categories.slug,
                COALESCE(SUM(order_items.line_total), 0) as revenue,
                COUNT(DISTINCT order_items.id) as order_count')
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'revenue' => round((float) $row->revenue, 2),
                'order_count' => (int) $row->order_count,
            ])
            ->values()
            ->all();
    }
}
