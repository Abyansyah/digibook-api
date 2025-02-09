<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingSession;
use App\Models\UserPoint;
use Illuminate\Http\Request;

class ReadingSessionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|exists:books,slug',
            'last_page' => 'required|integer|min:1',
        ]);

        $userId = auth('api')->id();
        $book = Book::where('slug', $validated['slug'])->first();

        $lastPage = ReadingSession::where('user_id', $userId)
            ->where('book_id', $book->id)
            ->value('last_page');

        if ($lastPage !== null) {
            if ($validated['last_page'] < $lastPage) {
                return response()->json([
                    'message' => 'Last page cannot be lower than the previous value.',
                    'success' => false
                ], 422);
            }

            if ($validated['last_page'] > $lastPage + 1) {
                return response()->json([
                    'message' => 'You can only proceed to the next page sequentially.',
                    'success' => false
                ], 422);
            }
        }

        ReadingSession::updateOrCreate(
            [
                'user_id' => $userId,
                'book_id' => $book->id,
            ],
            [
                'last_page' => $validated['last_page'],
            ]
        );

        if ($validated['last_page'] == $book->page_count) {
            UserPoint::create([
                'user_id' => $userId,
                'source_type' => 'book',
                'source_id' => $book->id,
                'points' => $book->page_count,
            ]);
        }


        return response()->json([
            'message' => 'Reading session saved successfully.',
            'success' => true
        ], 200);
    }



    public function show($userId, $bookId)
    {
        $readingSession = ReadingSession::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if (!$readingSession) {
            return response()->json(['message' => 'Reading session not found.'], 404);
        }

        return response()->json(['data' => $readingSession]);
    }
}
