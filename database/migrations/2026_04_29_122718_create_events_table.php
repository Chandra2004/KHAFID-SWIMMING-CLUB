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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('banner')->nullable();
            $table->string('logo_left')->nullable();
            $table->string('logo_right')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->integer('lane_count')->default(8);
            $table->enum('status', ['draft', 'upcoming', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->string('group_link')->nullable();
            
            $table->foreignUuid('author_uid')->constrained('users', 'uid')->onDelete('cascade');
            $table->string('payment_method_uid', 36)->nullable(); // Optional: link to payment methods table if exists
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
