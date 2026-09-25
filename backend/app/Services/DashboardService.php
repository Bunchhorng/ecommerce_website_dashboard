<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function metrics(): array
    {
        $paidOrders = fn ($query) => $query->where('payment_status', Order::PAYMENT_PAID);

        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $prevMonthStart = $monthStart->copy()->subMonth();
        $prevMonthEnd = $monthStart->copy()->subSecond();
        $weekStart = $now->copy()->startOfWeek();

        $revenue = fn ($from, $to) => $paidOrders(Order::query()
            ->where('placed_at', '>=', $from)
            ->where('placed_at', '<=', $to))
            ->sum('total');

        $ordersPerStatus = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $thisMonthOrders = Order::where('placed_at', '>=', $monthStart)->count();
        $lastMonthOrders = Order::where('placed_at', '>=', $prevMonthStart)
            ->where('placed_at', '<=', $prevMonthEnd)
            ->count();

        $thisMonthCustomers = User::where('role', User::ROLE_CUSTOMER)
            ->where('created_at', '>=', $monthStart)
            ->count();
        $lastMonthCustomers = User::where('role', User::ROLE_CUSTOMER)
            ->where('created_at', '>=', $prevMonthStart)
            ->where('created_at', '<=', $prevMonthEnd)
            ->count();

        $revenueDelta = $this->percentChange(
            $revenue($monthStart, $now),
            $revenue($prevMonthStart, $prevMonthEnd)
        );

        $monthRevenue = $revenue($monthStart, $now);

        return [
            'total_revenue' => round($paidOrders(Order::query())->sum('total'), 2),
            'today_revenue' => round($revenue($now->copy()->startOfDay(), $now), 2),
            'week_revenue' => round($revenue($weekStart, $now), 2),
            'month_revenue' => round($monthRevenue, 2),
            'revenue_delta' => $revenueDelta,
            'orders_count' => (int) $ordersPerStatus->sum(),
            'orders_delta' => $this->percentChange($thisMonthOrders, $lastMonthOrders),
            'pending_orders' => (int) ($ordersPerStatus[Order::STATUS_PENDING] ?? 0),
            'processing_orders' => (int) ($ordersPerStatus[Order::STATUS_PROCESSING] ?? 0),
            'completed_orders' => (int) ($ordersPerStatus[Order::STATUS_DELIVERED] ?? 0),
            'cancelled_orders' => (int) ($ordersPerStatus[Order::STATUS_CANCELLED] ?? 0),
            'customers_count' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'customers_delta' => $this->percentChange($thisMonthCustomers, $lastMonthCustomers),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'low_stock_products' => $this->lowStockQuery()->count(),
            'out_of_stock_products' => ProductVariant::query()
                ->where('is_active', true)
                ->whereHas('inventory', fn ($q) => $q->whereRaw('quantity - reserved_quantity <= 0'))
                ->count(),
        ];
    }

    public function revenueTrend(?Carbon $from = null, ?Carbon $to = null): array
    {
        if ($from === null || $to === null) {
            $to = Carbon::today();
            $from = Carbon::today()->subDays(29)->startOfDay();
        }

        $dayCount = (int) $from->diffInDays($to) + 1;

        $rows = Order::where('payment_status', Order::PAYMENT_PAID)
            ->where('placed_at', '>=', $from)
            ->where('placed_at', '<=', $to->copy()->endOfDay())
            ->selectRaw('DATE(placed_at) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $series = [];
        for ($i = 0; $i < $dayCount; $i++) {
            $date = $from->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $series[] = [
                'date' => $key,
                'revenue' => round((float) ($rows[$key] ?? 0), 2),
            ];
        }

        return $series;
    }

    public function ordersTrend(?Carbon $from = null, ?Carbon $to = null): array
    {
        if ($from === null || $to === null) {
            $to = Carbon::today();
            $from = Carbon::today()->subDays(29)->startOfDay();
        }

        $dayCount = (int) $from->diffInDays($to) + 1;

        $rows = Order::where('placed_at', '>=', $from)
            ->where('placed_at', '<=', $to->copy()->endOfDay())
            ->selectRaw('DATE(placed_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $series = [];
        for ($i = 0; $i < $dayCount; $i++) {
            $date = $from->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $series[] = [
                'date' => $key,
                'orders' => (int) ($rows[$key] ?? 0),
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

    public function paymentStatusDistribution(): array
    {
        return Payment::query()
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

    public function topSellingProducts(int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])
            ->whereNotNull('orders.placed_at')
            ->when($from !== null, fn ($q) => $q->where('orders.placed_at', '>=', $from))
            ->when($to !== null, fn ($q) => $q->where('orders.placed_at', '<=', $to->copy()->endOfDay()))
            ->selectRaw('order_items.product_id, order_items.product_name,
                SUM(order_items.quantity) as total_qty,
                SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'total_qty' => (int) $row->total_qty,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->values()
            ->all();
    }

    public function lowStockList(int $limit = 10): array
    {
        return $this->lowStockQuery()
            ->with(['variant.product.brand'])
            ->orderByRaw('(quantity - reserved_quantity) asc')
            ->limit($limit)
            ->get()
            ->map(fn (Inventory $inventory) => [
                'id' => $inventory->id,
                'product_id' => $inventory->variant?->product_id,
                'product_name' => $inventory->variant?->product?->name,
                'product_slug' => $inventory->variant?->product?->slug,
                'variant_name' => $inventory->variant?->name,
                'sku' => $inventory->variant?->sku,
                'quantity' => (int) $inventory->quantity,
                'reserved_quantity' => (int) $inventory->reserved_quantity,
                'available_quantity' => $inventory->available_quantity,
                'low_stock_threshold' => (int) $inventory->low_stock_threshold,
                'is_out_of_stock' => $inventory->available_quantity <= 0,
            ])
            ->values()
            ->all();
    }

    public function recentCustomers(int $limit = 5): array
    {
        return User::where('role', User::ROLE_CUSTOMER)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    public function recentReviews(int $limit = 5): array
    {
        return Review::query()
            ->with(['user', 'product'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'rating' => (float) $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'status' => $review->status,
                'user_name' => $review->user?->name,
                'product_name' => $review->product?->name,
                'created_at' => $review->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    public function recentPayments(int $limit = 5): array
    {
        return Payment::query()
            ->with('order')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'order_number' => $payment->order?->order_number,
                'method' => $payment->method,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'transaction_id' => $payment->transaction_id,
                'paid_at' => $payment->paid_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function lowStockQuery()
    {
        return Inventory::query()
            ->whereRaw('quantity - reserved_quantity <= low_stock_threshold');
    }

    protected function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
