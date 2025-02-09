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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_whatsapp')->nullable()->after('email');
            $table->date('tanggal_lahir')->nullable()->after('nomor_whatsapp');
            $table->enum('jenis_kelamin', ['MALE', 'FEMALE'])->nullable()->after('tanggal_lahir');
            $table->text('biografi')->nullable()->after('jenis_kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nomor_whatsapp', 'tanggal_lahir', 'jenis_kelamin', 'biografi']);
        });
    }
};
