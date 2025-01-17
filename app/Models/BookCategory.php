<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Post;

class BookCategory extends Model
{
    use HasFactory;


    protected $fillable = [
        'category_name',
        'description',
        'created_by',
        'image',
        'is_visible',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
