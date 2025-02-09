<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'status',
        'source',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
