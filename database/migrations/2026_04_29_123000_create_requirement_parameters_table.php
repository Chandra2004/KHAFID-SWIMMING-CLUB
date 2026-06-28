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
        Schema::create('requirement_parameters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('parameter_key', 100)->unique(); // e.g. 'gender', 'birth_year', 'skill_level'
            $table->enum('source', ['core', 'attribute'])->default('core'); // core: data_users, attribute: user_attributes
            $table->string('display_name', 255); // e.g. 'Jenis Kelamin', 'Tahun Lahir'
            $table->enum('input_type', ['text', 'number', 'select', 'date', 'range', 'boolean', 'email', 'tel', 'textarea', 'file'])->default('text');
            $table->json('input_options')->nullable(); // JSON: ["Putra", "Putri"] atau range [2010, 2015]
            $table->string('validation_rules')->nullable(); // Laravel validation strings, e.g. 'required|integer|min:2010'
            $table->string('error_message')->nullable(); // Custom error if validation fails
            $table->json('allowed_operators')->nullable(); // Operators like ["=", ">", "<", "IN"]
            $table->text('description')->nullable();
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
        Schema::dropIfExists('requirement_parameters');
    }
};
