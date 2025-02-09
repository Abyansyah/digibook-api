<?php

namespace App\Http\Resources\EventRegistration;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResources extends JsonResource
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
            'description' => $this->description,
            'event_overview' => $this->event_overview,
            'start_date' => Carbon::parse($this->start_date)->format('d F Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d F Y'),
            'start_time' => Carbon::parse($this->start_time)->format('H:i'),
            'end_time' => Carbon::parse($this->end_time)->format('H:i'),
            'location' => $this->location,
            'registration_start_date' => Carbon::parse($this->registration_start_date)->format('d F Y'),
            'registration_end_date' => Carbon::parse($this->registration_end_date)->format('d F Y'),
            'image' => url('storage/' . $this->image),
        ];
    }
}
