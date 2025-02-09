<?php

namespace App\Http\Controllers;

use App\Http\Resources\Book\BookCollection;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\ReadingSession;
use App\Models\UserPoint;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['categories', 'library', 'publication']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->has('book_categories')) {
            $categories = explode(',', $request->input('book_categories'));

            $query->whereIn('id', function ($subQuery) use ($categories) {
                $subQuery->select('book_id')
                    ->from('book_has_categories')
                    ->whereIn('book_category_id', function ($categoryQuery) use ($categories) {
                        $categoryQuery->select('id')
                            ->from('book_categories')
                            ->whereIn('name', $categories);
                    });
            });
        }

        $query->where(function ($q) {
            $q->whereDoesntHave('publication')
                ->orWhereHas('publication', function ($queryPub) {
                    $queryPub->where('status', 'published');
                });
        });

        $books = $query->paginate(12);

        return response()->success(new BookCollection($books), 'Books retrieved successfully', 200);
    }


   

    public function show($slug)
    {
        $book = Book::select('id', 'title', 'slug', 'author', 'isbn', 'stock', 'description', 'is_visible', 'price', 'image', 'library_id', 'added_by', 'created_at', 'updated_at', 'language', 'page_count', 'publisher', 'publication_year')
            ->with([
                'categories:name',
                'library:name,location',
                'addedBy:name'
            ])
            ->where('slug', $slug)
            ->first();

        if (!$book) {
            return response()->error(['message' => 'Book not found'], 404);
        }



        return response()->success(new BookResource($book), 'Book retrieved successfully');
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:book_categories,id',
            'library_id' => 'sometimes|exists:libraries,id',
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id,
            'stock' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
            'is_visible' => 'boolean',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable|string',
            'added_by' => 'sometimes|exists:users,id',
        ]);

        $book->update($validated);

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ], 200);
    }

    public function readBook($slug)
    {
        $book = Book::select('id', 'title', 'slug', 'page_count', 'book_file')
            ->where('slug', $slug)
            ->first();

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $user = auth()->user();
        $lastPage = ReadingSession::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->value('last_page');

        return response()->json([
            'book' => $book->title,
            'slug' => $book->slug,
            'page_count' => $book->page_count,
            'book_file' => url('book-files/' . basename($book->book_file)),
            'last_page' => $lastPage
        ]);
    }


    public function getBookFile($filename)
    {
        $path = storage_path("app/public/book-files/{$filename}");

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file($path, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => mime_content_type($path),
        ]);
    }

    public function getCategories()
    {
        $categories = BookCategory::select('id', 'name')->get();
        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully',
        ], 200);
    }
}
