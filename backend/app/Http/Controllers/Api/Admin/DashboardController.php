<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard) {}

    public function overview(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        return [
            'data' => [
                'range' => $request->query('range', '30'),
                'metrics' => $this->dashboard->metrics(),
                'revenue_trend' => $this->dashboard->revenueTrend($from, $to),
                'orders_trend' => $this->dashboard->ordersTrend($from, $to),
                'status_distribution' => $this->dashboard->orderStatusDistribution(),
                'payment_status_distribution' => $this->dashboard->paymentStatusDistribution(),
                'sales_by_category' => $this->dashboard->salesByCategory(),
                'top_selling_products' => $this->dashboard->topSellingProducts(5, $from, $to),
                'low_stock' => $this->dashboard->lowStockList(),
                'recent_customers' => $this->dashboard->recentCustomers(),
                'recent_reviews' => $this->dashboard->recentReviews(),
                'recent_payments' => $this->dashboard->recentPayments(),
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveRange(Request $request): array
    {
        $range = $request->query('range', '30');
        $now = Carbon::now();

        if ($range === 'custom' && $request->filled(['from', 'to'])) {
            return [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ];
        }

        return match ($range) {
            'today' => [$now->copy()->startOfDay(), $now],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            '7' => [$now->copy()->subDays(6)->startOfDay(), $now],
            'this_month' => [$now->copy()->startOfMonth(), $now],
            'last_month' => [$now->copy()->startOfMonth()->subMonth(), $now->copy()->startOfMonth()->subSecond()],
            default => [$now->copy()->subDays(29)->startOfDay(), $now],
        };
    }
}
