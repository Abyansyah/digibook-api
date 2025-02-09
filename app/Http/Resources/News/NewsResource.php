<?php

namespace App\Http\Resources\News;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
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
            'description' => $this->content,
            'image' => url('storage/' . $this->image),
            'is_visible' => $this->is_visible,
            'author' => $this->author->name,
            'category' => $this->category->category_name,
            'created_at' => $this->created_at ? $this->created_at->format('d-m-Y') : null,
        ];
    }
}
