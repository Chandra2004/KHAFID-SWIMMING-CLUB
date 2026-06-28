<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('name'); // e.g., 'Instagram Icon', 'Logo KSC'
            $table->string('file_path');
            $table->enum('type', ['icon', 'logo', 'banner', 'document_template'])->default('icon');
            $table->string('category')->nullable(); // e.g., 'social_media', 'sponsor'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
