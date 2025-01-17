<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookReview\BookReviewCollection;
use App\Http\Resources\BookReview\BookReviewResource;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Http\Request;

class BookReviewController extends Controller
{
    public function index($id)
    {
        $reviews = BookReview::where('book_id', $id)->with('user')->paginate(10);

        return response()->success(new BookReviewCollection($reviews), 'Reviews fetched successfully.', 200);
    }

    public function store(Request $request, Book $book)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->success('Review added successfully.', 201);
    }

    public function destroy(BookReview $review)
    {
        if ($review->user_id !== auth()->id()) {
            return response()->error(['message' => 'Unauthorized'], 403);
        }

        $review->delete();
        return response()->success([], 'Review deleted successfully.');
    }
}
