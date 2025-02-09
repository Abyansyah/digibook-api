<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'author',
        'publisher',
        'page_count',
        'publication_year',
        'language',
        'library_id',
        'isbn',
        'stock',
        'description',
        'is_visible',
        'price',
        'image',
        'added_by',
        'book_file',

    ];

    public function categories(): BelongsToMany
    {
        return $this->BelongsToMany(BookCategory::class, 'book_has_categories', 'book_id',  'book_category_id');
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class, 'library_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function readingHistories()
    {
        return $this->hasMany(ReadingSession::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function publication()
    {
        return $this->hasOne(Publication::class);
    }
}
