<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Event;
use App\Models\UserPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPointController extends Controller
{
    public function topUsers()
    {
        $topUsers = UserPoint::select('user_id')
            ->selectRaw('SUM(points) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(10)
            ->with('user')
            ->get();

        return response()->json(['data' => $topUsers]);
    }


    public function complete(Request $request)
    {
        $userId = Auth::id();
        $sourceType = $request->input('source_type');
        $sourceId = $request->input('source_id');
        $points = $request->input('points');

        if ($sourceType === 'book') {
            $exists = Book::where('id', $sourceId)->exists();
            if (!$exists) {
                return response()->json(['message' => 'Buku tidak ditemukan!'], 404);
            }
        } elseif ($sourceType === 'event') {
            $exists = Event::where('id', $sourceId)->exists();
            if (!$exists) {
                return response()->json(['message' => 'Event tidak ditemukan!'], 404);
            }
        } else {
            return response()->json(['message' => 'Jenis sumber tidak valid!'], 400);
        }

        $existingPoint = UserPoint::where('user_id', $userId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->exists();

        if ($existingPoint) {
            return response()->json(['message' => 'Poin sudah pernah ditambahkan untuk sumber ini!'], 400);
        }

        UserPoint::create([
            'user_id' => $userId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'points' => $points
        ]);

        return response()->json(['message' => 'Poin berhasil ditambahkan!']);
    }
}
