<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('book_categories')->cascadeOnDelete();
            $table->foreignId('library_id')->constrained('libraries')->cascadeOnDelete();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->unique();
            $table->integer('stock');
            $table->longText('description')->nullable();
            $table->boolean('is_visible')->default(false);
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
