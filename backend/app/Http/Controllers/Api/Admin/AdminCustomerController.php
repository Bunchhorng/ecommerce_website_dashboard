<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderListResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_CUSTOMER);

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->q) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $paginator = $query->orderByDesc('id')->paginate(15);

        return [
            'data' => UserResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function show(Request $request, User $user)
    {
        $ordersCount = $user->orders()->count();
        $lifetimeSpend = (float) $user->orders()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->sum('total');

        $recentOrders = $user->orders()
            ->with('items')
            ->withCount('items')
            ->latest('placed_at')
            ->limit(5)
            ->get();

        return [
            'user' => new UserResource($user),
            'orders_count' => $ordersCount,
            'lifetime_spend' => round($lifetimeSpend, 2),
            'recent_orders' => OrderListResource::collection($recentOrders),
        ];
    }
}
