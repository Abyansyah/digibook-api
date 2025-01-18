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
        Schema::table('events', function (Blueprint $table) {
            $table->json('event_mode')->after('status')->default(json_encode(['Offline']));
            $table->string('location')->nullable()->after('event_mode');
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('event_mode');
            $table->dropColumn('location');
            $table->decimal('price', 10, 2)->default(0);
        });
    }
};
