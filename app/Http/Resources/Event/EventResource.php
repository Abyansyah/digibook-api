<?php

namespace App\Http\Resources\Event;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'description' => $this->description,
            'start_date' => Carbon::parse($this->start_date)->format('d F Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d F Y'),
            'status' => $this->status,
            'event_mode' => $this->event_mode,
            'location' => $this->location,
            'participants_count' => $this->participants_count,
            'i_registration' => $this->when(
                auth('api')->check(),
                fn() => $this->registrations()->where('user_id', auth('api')->id())->exists()
            ),
            'category' => $this->eventType->name,
            'imageUrl' => $this->image ? url('storage/' . $this->image) : null,
            'registeredCount' => $this->registrations()->count(),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
