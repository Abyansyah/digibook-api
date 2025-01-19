<?php

namespace App\Http\Controllers;

use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\Event\EventResource;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Event::with(['eventType']);

            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->input('search') . '%');
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('event_category')) {
                $query->whereHas('eventType', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('event_category') . '%');
                });
            }

            if ($request->has('event_mode')) {
                $query->whereJsonContains('event_mode', $request->input('event_mode'));
            }

            $events = $query->paginate(10);

            $user = auth('api')->user();

            if ($user) {
                foreach ($events->items() as $event) {
                    $event->i_registration = $event->registrations()->where('user_id', $user->id)->exists();
                }
            }

            return response()->success(new EventCollection($events), 'Events retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }


    public function show($id)
    {
        try {
            $event = Event::with(['eventType'])->findOrFail($id);

            return response()->success(new EventResource($event), 'Event retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }
}
