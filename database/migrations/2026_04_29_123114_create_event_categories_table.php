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
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('event_uid')->constrained('events', 'uid')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('category_uid')->nullable()->constrained('categories', 'uid')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('acara_number')->nullable(); // e.g., 101 or 01
            $table->string('acara_name')->nullable(); // e.g., '50M KICKING BEBAS PUTRA'
            $table->foreignUuid('parameter_uid')->nullable()->constrained('requirement_parameters', 'uid')->onDelete('set null'); // Parameter Utama
            $table->string('operator')->nullable()->default('='); // Operator for Parameter Utama
            $table->string('parameter_value')->nullable(); // Nilai dari Parameter Utama
            $table->text('main_requirement')->nullable(); // Syarat Utama yang muncul di dokumen
            
            $table->enum('type', ['free', 'paid'])->default('paid');
            $table->decimal('registration_fee', 12, 2)->default(0.00);
            $table->integer('total_series')->default(1); // Jumlah Seri
            
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('group_link')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
