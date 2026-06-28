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
        Schema::create('social_medias', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('user_uid')->constrained('users', 'uid')->onDelete('cascade');
            $table->foreignUuid('asset_uid')->nullable()->constrained('assets', 'uid')->onDelete('set null'); // Link ke icon di tabel assets
            
            $table->string('platform_name'); // Nama tampilan, e.g. 'Instagram'
            $table->string('link');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_medias');
    }
};
