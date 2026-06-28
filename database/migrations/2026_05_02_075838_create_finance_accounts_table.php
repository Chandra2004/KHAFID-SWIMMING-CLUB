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
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('bank_name'); // e.g., BCA, Mandiri, Cash
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('image')->nullable(); // For QRIS or other images
            $table->string('description')->nullable();
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
        Schema::dropIfExists('finance_accounts');
    }
};
