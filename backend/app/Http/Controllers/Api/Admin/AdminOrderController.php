<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderTransitionRequest;
use App\Http\Resources\OrderListResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(protected OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $query = Order::with(['items', 'user'])->withCount('items')->orderByDesc('placed_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->q) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        $paginator = $query->paginate(15);

        return [
            'data' => OrderListResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['items', 'payment', 'shipments']));
    }

    public function transition(OrderTransitionRequest $request, Order $order)
    {
        $order = $this->orders->transition($order, $request->status);

        return new OrderResource($order);
    }
}
