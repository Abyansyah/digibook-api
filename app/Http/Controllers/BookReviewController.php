<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookReview\BookReviewCollection;
use App\Http\Resources\BookReview\BookReviewResource;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Http\Request;

class BookReviewController extends Controller
{
    public function index($slug)
    {
        try {
            $book = Book::where('slug', $slug)->firstOrFail();

            if (!$book) {
                return response()->error(['message' => 'Book not found'], 404);
            }

            $reviews = BookReview::where('book_id', $book->id)->with('user')->paginate(10);

            return response()->success(new BookReviewCollection($reviews), 'Reviews fetched successfully.', 200);
        } catch (\Exception $e) {
            return response()->error(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request, $slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        $existingReview = $book->reviews()->where('user_id', auth()->id())->first();
        if ($existingReview) {
            return response()->json(['message' => 'Anda sudah memberikan review untuk buku ini', "success" => true,],   200);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->success([], 'Review added successfully.', 201);
    }

    public function destroy($slug, $reviewId)
    {
        $book = Book::where('slug', $slug)->firstOrFail();
        $review = BookReview::where('id', $reviewId)
            ->where('book_id', $book->id)
            ->firstOrFail();

        if ($review->user_id !== auth()->id()) {
            return response()->error(['message' => 'Unauthorized'], 403);
        }

        $review->delete();
        return response()->success([], 'Review deleted successfully.');
    }
}
