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
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('event_uid')->nullable()->constrained('events', 'uid')->onDelete('cascade');
            
            $table->string('key', 100);
            $table->string('type', 50)->default('string');
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_editable')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_settings');
    }
};
