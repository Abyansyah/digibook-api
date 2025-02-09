<?php

namespace App\Http\Resources\Book;

use App\Models\ReadingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'stock' => $this->stock,
            'description' => $this->description,
            'is_visible' => $this->is_visible,
            'price' => $this->price,
            'image' => url('storage/' . $this->image),
            'categories' => $this->categories->map(function ($category) {
                return [
                    'name' => $category->name,
                ];
            }),
            'book_file' => url('storage/' . $this->book_file),
            'library' => [
                'name' => $this->library->name ?? null,
                'location' => $this->library->location ?? null,
            ],
            'read_count' => $this->readingHistories()->count(),
            'average_rating' => round($this->average_rating, 2),
            'review_count' => $this->review_count,
            'publisher' => $this->publisher ?? null,
            'page_count' => $this->page_count,
            'publication_year' => $this->publication_year,
            'language' => $this->language,
            'added_by' => $this->addedBy->name ?? null,
            'last_page' => $this->when(
                auth('api')->check(),
                fn() => ReadingSession::where('user_id', auth('api')->id())->where('book_id', $this->id)->value('last_page')
            ),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
