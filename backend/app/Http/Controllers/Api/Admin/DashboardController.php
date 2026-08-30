<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard)
    {
    }

    public function overview(Request $request)
    {
        $days = (int) $request->query('days', 30);

        return [
            'data' => [
                'metrics' => $this->dashboard->metrics(),
                'revenue_trend' => $this->dashboard->revenueTrend($days),
                'status_distribution' => $this->dashboard->orderStatusDistribution(),
                'sales_by_category' => $this->dashboard->salesByCategory(),
            ],
        ];
    }
}
