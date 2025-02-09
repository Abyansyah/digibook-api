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
        Schema::table('books', function (Blueprint $table) {
            $table->string('publisher')->nullable()->after('author');
            $table->integer('page_count')->nullable()->after('publisher');
            $table->year('publication_year')->nullable()->after('page_count');
            $table->string('language')->nullable()->after('publication_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'publisher')) {
                $table->dropColumn('publisher');
            }
            if (Schema::hasColumn('books', 'page_count')) {
                $table->dropColumn('page_count');
            }
            if (Schema::hasColumn('books', 'publication_year')) {
                $table->dropColumn('publication_year');
            }
            if (Schema::hasColumn('books', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
};
