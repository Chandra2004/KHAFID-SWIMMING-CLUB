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
        Schema::create('data_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('user_uid')->constrained('users', 'uid')->onDelete('cascade');

            // Data Pribadi Dasar
            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();

            // Kontak & Alamat
            $table->string('phone_number')->nullable();
            $table->string('backup_phone_number')->nullable();
            $table->text('address')->nullable();

            // Data Fisik (Spesifikasi Atlet)
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('weight', 5, 2)->nullable(); // kg

            // Identitas & Dokumen
            $table->string('identity_number', 20)->nullable(); // NIK / KTP
            $table->string('profile_picture')->nullable();
            $table->string('identity_photo')->nullable(); // Foto KTP
            $table->string('birth_certificate_photo')->nullable(); // Foto Akta
            $table->string('family_card_photo')->nullable(); // Foto KK

            // Data Kesehatan (Krusial untuk keselamatan di air)
            $table->text('medical_history')->nullable(); // Riwayat asma, jantung, dll


            // Status Klub
            $table->foreignUuid('club_uid')->nullable()->constrained('clubs', 'uid')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_users');
    }
};
