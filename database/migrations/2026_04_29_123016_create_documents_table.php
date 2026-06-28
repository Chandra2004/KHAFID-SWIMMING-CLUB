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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('event_uid')->nullable()->constrained('events', 'uid')->onDelete('cascade');

            // Template Identity
            $table->string('name');                              // Template name (e.g., "Buku Acara KSC 2026")
            $table->string('type', 100)->default('other');       // Custom type: result, schedule, invoice, certificate, etc.
            $table->string('title')->nullable();                 // Document header title
            $table->text('description')->nullable();             // Subtitle / keterangan

            // Branding
            $table->string('logo_left', 255)->nullable();
            $table->string('logo_right', 255)->nullable();

            // Data Builder Config (JSON)
            $table->string('data_source', 100)->nullable();      // Table source: results, schedules, payments, etc.
            $table->json('selected_columns')->nullable();        // Array of {key, label} for columns
            $table->json('layout_settings')->nullable();         // Page, header, table, footer config

            // Typography & Page Settings
            $table->string('font_family', 50)->default('Arial'); // Montserrat, Roboto, Arial, Times New Roman
            $table->integer('font_size')->default(10);
            $table->string('page_size', 20)->default('A4');
            $table->string('page_orientation', 20)->default('portrait');

            // Content & Export
            $table->text('content')->nullable();                 // Body text with placeholders
            $table->string('file_path')->nullable();             // Last generated file path
            $table->string('export_format', 20)->default('pdf'); // pdf, excel

            // Status
            $table->boolean('is_public')->default(false);
            $table->decimal('total_price', 15, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
