<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'category_id',
        'library_id',
        'isbn',
        'stock',
        'description',
        'is_visible',
        'image',
        'price',
        'added_by',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
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
}
