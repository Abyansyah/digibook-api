<?php

namespace App\Http\Controllers;

use App\Http\Resources\News\NewsCollection;
use App\Http\Resources\News\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = News::with(['author', 'category'])->where('is_visible', 1);

            if ($request->has('title')) {
                $query->where('title', 'like', '%' . $request->input('title') . '%');
            }

            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->has('author_id')) {
                $query->where('author_id', $request->input('author_id'));
            }

            $news = $query->paginate(10);

            return response()->success(new NewsCollection($news), 'News retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $news = News::with(['author', 'category'])->findOrFail($id);

            return response()->success(new NewsResource($news), 'News retrieved successfully');
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }
}
