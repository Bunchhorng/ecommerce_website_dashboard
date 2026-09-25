<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->notifications()->latest();

        if ($request->filled('filter')) {
            match ($request->filter) {
                'unread' => $query->whereNull('read_at'),
                'read' => $query->whereNotNull('read_at'),
                default => null,
            };
        }

        $paginator = $query->paginate(20);

        return [
            'data' => collect($paginator->items())->map(fn ($n) => $this->format($n))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ];
    }

    public function unreadCount(Request $request)
    {
        return ['data' => ['unread_count' => $request->user()->unreadNotifications()->count()]];
    }

    public function markRead(Request $request, string $notificationId)
    {
        $user = $request->user();

        if ($notificationId === 'all') {
            $user->unreadNotifications()->update(['read_at' => now()]);

            return ['data' => ['message' => 'All notifications marked as read.']];
        }

        $notification = $user->notifications()->whereKey($notificationId)->first();

        if ($notification === null) {
            abort(404, 'Notification not found.');
        }

        $notification->markAsRead();

        return ['data' => ['message' => 'Notification marked as read.']];
    }

    public function destroy(Request $request, string $notificationId)
    {
        $user = $request->user();

        $notification = $user->notifications()->whereKey($notificationId)->first();

        if ($notification === null) {
            abort(404, 'Notification not found.');
        }

        $notification->delete();

        return response()->noContent();
    }

    protected function format($notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $this->friendlyType($notification->type),
            'title' => $notification->data['title'] ?? null,
            'message' => $notification->data['message'] ?? null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }

    protected function friendlyType(string $notificationClass): string
    {
        return match (true) {
            str_contains($notificationClass, 'Order') => 'order',
            str_contains($notificationClass, 'Review') => 'review',
            str_contains($notificationClass, 'LowStock'), str_contains($notificationClass, 'Inventory') => 'inventory',
            str_contains($notificationClass, 'Promo') => 'promo',
            default => 'system',
        };
    }
}
