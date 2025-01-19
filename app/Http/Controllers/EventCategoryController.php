<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function index()
    {
        $categories = EventCategory::select('id', 'name')->get();

        return response()->success($categories, 'Event categories retrieved successfully', 200);
    }
}
