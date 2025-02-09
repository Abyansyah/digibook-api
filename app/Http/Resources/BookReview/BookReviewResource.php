<?php

namespace App\Http\Resources\BookReview;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookReviewResource extends JsonResource
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
            'user' => $this->user->name,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'avatar' => $this->user->foto,
            'date' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
