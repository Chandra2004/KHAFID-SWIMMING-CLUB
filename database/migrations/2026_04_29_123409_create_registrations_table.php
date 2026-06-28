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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('user_uid')->constrained('users', 'uid')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('event_category_uid')->constrained('event_categories', 'uid')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('seed_time', 50)->nullable();
            $table->timestamp('entry_time')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, rejected
            $table->string('registration_number', 100)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
