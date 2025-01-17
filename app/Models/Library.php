<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'library_name',
        'address',
        'contact_number',
        'description',
        'opening_time',
        'closing_time',
        'is_visible',
        'image',
        'head_librarian_id',
    ];

    public function headLibrarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_librarian_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
