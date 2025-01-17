<?php

namespace App\Http\Controllers;

use App\Http\Resources\Book\BookCollection;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with(['category', 'library']);

        // Apply search filters
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }


        $books = $query->paginate(10);

        return response()->success(new BookCollection($books), 'Books retrieved successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'library_id' => 'exists:libraries,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_visible' => 'boolean',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'added_by' => 'required|exists:users,id',
        ]);

        $book = Book::create($validated);

        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $book = Book::select('id', 'title', 'author', 'isbn', 'stock', 'description', 'is_visible', 'price', 'image', 'category_id', 'library_id', 'added_by', 'created_at', 'updated_at')
            ->with([
                'category:id,category_name',
                'library:id,name,location',
                'addedBy:id,name'
            ])
            ->find($id);

        if (!$book) {
            return response()->error(['message' => 'Book not found'], 404);
        }

        return response()->success(new BookResource($book), 'Book retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ], 200);
    }
}
