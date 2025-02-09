<?php

namespace App\Http\Controllers;

use App\Filament\Resources\EventResource;
use App\Http\Resources\EventRegistration\EventRegistrationCollection;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $userId = auth()->id();

        $existingRegistration = EventRegistration::where('user_id', $userId)
            ->where('event_id', $validated['event_id'])
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'message' => 'You are already registered for this event.',
            ], 400);
        }

        EventRegistration::create([
            'user_id' => $userId,
            'event_id' => $validated['event_id'],
        ]);

        return response()->json([
            'message' => 'Successfully registered for the event.',
        ], 201);
    }

    public function index()
    {
        $userId = auth()->id();

        $registrations = EventRegistration::with('event')
            ->where('user_id', $userId)
            ->get();

        return response()->success(
            new EventResource($registrations),
            'Event registrations retrieved successfully',
        );
    }
}
