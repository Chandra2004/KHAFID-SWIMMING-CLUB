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
        Schema::create('user_attributes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('user_uid')->constrained('users', 'uid')->onDelete('cascade');
            
            $table->string('name', 100); // e.g., 'shoe_size'
            $table->string('type', 50)->default('string'); // string, number, date, boolean, json
            $table->text('value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_attributes');
    }
};
