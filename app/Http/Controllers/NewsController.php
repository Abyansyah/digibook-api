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

            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->input('search') . '%');
            }

            if ($request->has('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('category_name', 'like', '%' . $request->input('category') . '%');
                });
            }

            if ($request->has('author_id')) {
                $query->where('author_id', $request->input('author_id'));
            }

            $perPage = $request->input('per_page', 10);
            $news = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->success(new NewsCollection($news), 'News retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }

    public function show($slug)
    {
        try {
            $news = News::with(['author', 'category'])->where('slug', $slug)->firstOrFail();

            return response()->success(new NewsResource($news), 'News retrieved successfully');
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }

    public function getBanner()
    {
        try {
            $query = News::with(['author', 'category'])->where('is_visible', 1)->orderBy('created_at', 'desc');

            $news = $query->paginate(5);

            if ($news->isEmpty()) {
                return response()->error(['message' => 'No banners found'], 404);
            }

            return response()->success(new NewsCollection($news), 'News retrieved successfully');
        } catch (\Throwable $th) {
            return response()->error(['message' => $th->getMessage()], 500);
        }
    }
}
