<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'list' => $this->collection,
            'meta' => [
                'total' => $this->total() ?? null,
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'per_page' => $this->perPage(),
            ],
        ];
    }
}
