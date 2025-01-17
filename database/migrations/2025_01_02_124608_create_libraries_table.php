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
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('library_name');
            $table->string('address');
            $table->string('contact_number');
            $table->text('description')->nullable();
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_visible')->default(false);
            $table->string('image')->nullable();
            $table->foreignId('head_librarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
};
