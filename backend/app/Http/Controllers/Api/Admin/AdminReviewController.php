<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function __construct(protected ReviewService $reviews)
    {
    }

    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $paginator = $query->paginate(15);

        return [
            'data' => ReviewResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function approve(Review $review)
    {
        $this->reviews->approve($review);

        return new ReviewResource($review->fresh(['user', 'product']));
    }

    public function reject(Review $review)
    {
        $this->reviews->reject($review);

        return new ReviewResource($review->fresh(['user', 'product']));
    }
}
