<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookPubliCation;
use App\Http\Controllers\BookReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ReadingHistoryController;
use App\Http\Controllers\ReadingSessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
    'prefix' => 'v1',

], function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        Route::middleware('auth.api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth.api')->group(function () {
        Route::prefix('books')->group(function () {
            Route::put('/{id}', [BookController::class, 'update']);
            Route::delete('/{id}', [BookController::class, 'destroy']);
            Route::post('/{book}/reviews', [BookReviewController::class, 'store']);
            Route::delete('reviews/{review}', [BookReviewController::class, 'destroy']);
        });

        Route::prefix('events-detail')->group(function () {
            Route::get('/', [EventRegistrationController::class, 'index']);
            Route::post('/', [EventRegistrationController::class, 'store']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'show']);
            Route::put('/', [UserController::class, 'update']);
        });

        Route::prefix('user-points')->group(function () {
            Route::get('/', [UserPointController::class, 'topUsers']);
            Route::post('/', [UserPointController::class, 'complete']);
        });

        Route::prefix('reading-session')->group(function () {
            Route::post('', [ReadingSessionController::class, 'store']);
            Route::get('/{slug}', [BookController::class, 'readBook']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('/poin', [DashboardController::class, 'getPoinUser']);
            Route::get('/attendance-event', [DashboardController::class, 'getAttendanceEvent']);
            Route::get('/book-read', [DashboardController::class, 'getBookRead']);
            Route::get('/recent-event', [DashboardController::class, 'getRecentEvent']);
            Route::get('/recent-book', [DashboardController::class, 'getRecentBook']);
            Route::get('/event', [DashboardController::class, 'getEvent']);
            Route::get('/book', [DashboardController::class, 'getBook']);
        });

        Route::prefix('published')->group(function () {
            Route::get('/', [BookPubliCation::class, 'index']);
            Route::get('/{slug}', [BookPubliCation::class, 'getUser']);
            Route::post('/', [BookPubliCation::class, 'addBook']);
        });
        Route::post('/ppp/{slug}', [BookPubliCation::class, 'update']);
    });

    Route::prefix('books')->group(function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('/{id}', [BookController::class, 'show']);
        Route::get('/{id}/reviews', [BookReviewController::class, 'index']);
    });

    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/{slug}', [NewsController::class, 'show']);
        Route::patch('/banner', [NewsController::class, 'getBanner']);
    });

    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::get('/{id}', [EventController::class, 'show']);
    });
    Route::get('/book-categories', [BookController::class, 'getCategories']);
    Route::get('/event-categories', [EventCategoryController::class, 'index']);
});
