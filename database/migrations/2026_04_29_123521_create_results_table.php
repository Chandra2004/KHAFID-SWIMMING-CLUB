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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('registration_uid')->constrained('registrations', 'uid')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('final_time', 50)->nullable(); // Format MM:SS.ss
            $table->integer('total_milliseconds')->nullable(); // For easy sorting/filtering
            $table->string('status', 20)->default('FINISH'); // FINISH, NS, DSQ
            $table->unsignedInteger('rank')->nullable();
            $table->string('official_name', 100)->nullable(); // Signatory
            $table->decimal('score', 10, 2)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
