<?php

namespace App\Http\Controllers;

use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookPubliCation extends Controller
{

    public function index()
    {
        $userId = auth()->id();

        $booksImageUrl = url('book-image');
        $booksFileUrl = url('book-files');

        $query = DB::table('books')
            ->join('users', 'books.added_by', '=', 'users.id')
            ->join('publications', 'books.id', '=', 'publications.book_id')
            ->select(
                'books.id',
                'books.title',
                'books.slug',
                'books.author',
                'books.isbn',
                'books.description',
                DB::raw("CONCAT('{$booksImageUrl}/', SUBSTRING_INDEX(books.image, '/', -1)) as image"),
                'publications.status',
            )->where('books.added_by', $userId)->get();

        return response()->json([
            'data' => $query,
            'success' => true,
            'message' => 'Books retrieved successfully',
        ], 200);
    }

    public function getUser($slug)
    {
        $userId = auth()->id();
        $booksImageUrl = url('book-image');
        $booksFileUrl = url('book-files');

        $book = Book::with('categories')
            ->where('added_by', $userId)
            ->where('slug', $slug)
            ->first();

        if ($book) {
            $book->image = $booksImageUrl . '/' . last(explode('/', $book->image));
            $book->book_file = $booksFileUrl . '/' . last(explode('/', $book->book_file));
        }

        return response()->json([
            'data'    => $book,
            'success' => true,
            'message' => 'Book retrieved successfully',
        ], 200);
    }

    public function addBook(Request $request)
    {
        $validated = $request->validate([
            'category_id'            => 'sometimes|nullable|exists:book_categories,id',
            'library_id'             => 'sometimes|nullable|exists:libraries,id',
            'title'                  => 'required|nullable|string|max:255',
            'author'                 => 'sometimes|nullable|string|max:255',
            'isbn'                   => 'sometimes|nullable|string',
            'stock'                  => 'sometimes|nullable|integer|min:0',
            'description'            => 'sometimes|nullable|string',
            'is_visible'             => 'sometimes|nullable|boolean',
            'price'                  => 'sometimes|nullable|numeric|min:0',
            'image'                  => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:20480',
            'added_by'               => 'sometimes|nullable|exists:users,id',
            'page_count'             => 'sometimes|nullable|integer|min:0',
            'language'               => 'sometimes|nullable|string',
            'book_file'              => 'sometimes|nullable|file|mimes:pdf,epub,doc,docx|max:20480',
            'stock'                  => 'sometimes|nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('book_file')) {
            $file = $request->file('book_file');
            $randomString = Str::random(20);
            $originalName = $file->getClientOriginalName();
            $newFileName = $randomString . '_' . $originalName;
            $path = $file->storeAs('book-files', $newFileName, 'public');
            $validated['book_file'] = $path;
        }

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $randomString = Str::random(20);
            $originalImageName = $imageFile->getClientOriginalName();
            $newImageName = $randomString . '_' . $originalImageName;
            $imagePath = $imageFile->storeAs('image-books', $newImageName, 'public');
            $validated['image'] = $imagePath;
        }

        $validated['added_by'] = auth()->id();

        $book = Book::create($validated);

        $book->publication()->create([
            'status' => 'draft'
        ]);

        return response()->success(new BookResource($book), 'Book created successfully.', 201);
    }

    public function update(Request $request, $slug)
    {
        $book = Book::where('slug', $slug)
            ->where('added_by', auth()->id())
            ->with('publication')
            ->firstOrFail();

        if (!$book->publication) {
            return response()->json([
                'success' => false,
                'message' => 'Book cannot be updated because it is not published.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id'            => 'sometimes|nullable|exists:book_categories,id',
            'title'                  => 'sometimes|nullable|string|max:255',
            'author'                 => 'sometimes|nullable|string|max:255',
            'isbn'                   => 'sometimes|nullable|string|unique:books,isbn,' . $book->id,
            'stock'                  => 'sometimes|nullable|integer|min:0',
            'description'            => 'sometimes|nullable|string',
            'price'                  => 'sometimes|nullable|numeric|min:0',
            'image'                  => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:20480',
            'publication_status'     => 'sometimes|nullable|in:draft,submitted,approved,published,rejected',
            'page_count'             => 'sometimes|nullable|integer|min:0',
            'language'               => 'sometimes|nullable|string',
            'book_file'              => 'sometimes|nullable|file|mimes:pdf,epub,doc,docx|max:20480',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title'], '-');
        }

        if ($request->hasFile('image')) {
            if ($book->image && Storage::disk('public')->exists($book->image)) {
                Storage::disk('public')->delete($book->image);
            }
            $imageFile = $request->file('image');
            $randomString = Str::random(20);
            $originalImageName = $imageFile->getClientOriginalName();
            $newImageName = $randomString . '_' . $originalImageName;
            $imagePath = $imageFile->storeAs('image-books', $newImageName, 'public');
            $validated['image'] = $imagePath;
        }

        if ($request->hasFile('book_file')) {
            if ($book->book_file && Storage::disk('public')->exists($book->book_file)) {
                Storage::disk('public')->delete($book->book_file);
            }
            $file = $request->file('book_file');
            $randomString = Str::random(20);
            $originalName = $file->getClientOriginalName();
            $newFileName = $randomString . '_' . $originalName;
            $path = $file->storeAs('book_files', $newFileName, 'public');
            $validated['book_file'] = $path;
        }

        $book->update($validated);

        $publicationData = [];
        if ($request->has('publication_status')) {
            $publicationData['status'] = $request->input('publication_status');
        }
        if ($request->has('publication_source')) {
            $publicationData['source'] = $request->input('publication_source');
        }
        if (!empty($publicationData)) {
            $book->publication()->update($publicationData);
        }

        $book->load('publication');

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully.',
            'data'    => $book,
        ], 200);
    }
}
