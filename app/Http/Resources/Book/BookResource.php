<?php

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'author' => $this->author,
            'isbn' => $this->isbn,
            'stock' => $this->stock,
            'description' => $this->description,
            'is_visible' => $this->is_visible,
            'price' => $this->price,
            'image' => url('storage/' . $this->image),
            'category' => $this->category->category_name ?? null,
            'library' => [
                'id' => $this->library->id ?? null,
                'name' => $this->library->name ?? null,
                'location' => $this->library->location ?? null,
            ],
            'added_by' => $this->addedBy->name ?? null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
