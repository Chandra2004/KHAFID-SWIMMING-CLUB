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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('name'); // Nama Lengkap Klub
            $table->string('short_name', 50)->nullable(); // Singkatan, e.g., 'DELTA SC'
            $table->string('logo')->nullable();
            $table->string('coach_name')->nullable(); // Nama Pelatih Kepala Klub tersebut
            $table->string('contact')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
