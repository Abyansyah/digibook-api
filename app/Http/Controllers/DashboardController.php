<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\ReadingSession;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getPoinUser()
    {
        $user = auth()->user();
        $userPoin = UserPoint::where('user_id', $user->id)->sum('points');

        return response()->json(['poin' => $userPoin], 200);
    }

    public function getAttendanceEvent()
    {
        $user = auth()->user();
        $userEvent = EventRegistration::where('user_id', $user->id)->count();

        return response()->json(['event' => $userEvent], 200);
    }

    public function getBookRead()
    {
        $user = auth()->user();
        $userBook = ReadingSession::where('user_id', $user->id)->count();

        return response()->json(['book' => $userBook], 200);
    }

    public function getRecentEvent()
    {
        $user = auth()->user();
        $userEvent = DB::table('event_registrations')
            ->join('events', 'event_registrations.event_id', '=', 'events.id')
            ->select('event_registrations.id', 'events.title as name', 'event_registrations.created_at as date', 'events.status')
            ->where('event_registrations.user_id', $user->id)
            ->limit(3)
            ->orderBy('event_registrations.created_at', 'desc')
            ->get();

        return response()->json(['data' => $userEvent], 200);
    }

    public function getRecentBook()
    {
        $user = auth()->user();
        $userBook = DB::table('reading_sessions')
            ->join('books', 'reading_sessions.book_id', '=', 'books.id')
            ->select(
                'books.title',
                'reading_sessions.created_at',
                'reading_sessions.last_page',
                'books.page_count',

                DB::raw('ROUND((reading_sessions.last_page / books.page_count) * 100, 1) as presentase'),
            )
            ->where('reading_sessions.user_id', $user->id)
            ->orderBy('reading_sessions.created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json(['data' => $userBook], 200);
    }

    public function getEvent(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab');

        $query = DB::table('event_registrations')
            ->join('events', 'event_registrations.event_id', '=', 'events.id')
            ->select(
                'event_registrations.id',
                'events.title as name',
                DB::raw('DATE(event_registrations.created_at) as date'),
                'events.status',
                DB::raw('DATE_FORMAT(events.start_time, "%H:%i") as time'),
                'events.slug',
                'events.location',
                DB::raw('(SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = events.id) as total_participant')
            )
            ->where('event_registrations.user_id', $user->id);

        if ($tab === 'ongoing') {
            $query->where('events.status', 'ongoing');
        } elseif ($tab === 'completed') {
            $query->where('events.status', 'completed');
        }

        $userEvent = $query->get();

        return response()->json(['data' => $userEvent], 200);
    }

    public function getBook(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab');

        $bookFilesUrl = url('/book-image');

        $query = DB::table('reading_sessions')
            ->join('books', 'reading_sessions.book_id', '=', 'books.id')
            ->select(
                'reading_sessions.id',
                'books.title',
                DB::raw('DATE(reading_sessions.created_at) as date'),
                'reading_sessions.last_page',
                'books.page_count',
                'books.slug',
                'books.author',
                DB::raw('ROUND((reading_sessions.last_page / books.page_count) * 100, 1) as presentase'),
                DB::raw("CONCAT('{$bookFilesUrl}/', SUBSTRING_INDEX(books.image, '/', -1)) as image")
            )
            ->where('reading_sessions.user_id', $user->id);

        if ($tab === 'completed') {
            $query->whereColumn('reading_sessions.last_page', 'books.page_count');
        }

        $userBook = $query->get();

        return response()->json(['data' => $userBook], 200);
    }


    public function getImageBook($filename)
    {
        $path = storage_path("app/public/image-books/{$filename}");

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file($path, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => mime_content_type($path),
        ]);
    }
}
