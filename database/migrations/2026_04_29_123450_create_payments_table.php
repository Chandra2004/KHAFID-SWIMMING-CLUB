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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignUuid('registration_uid')->constrained('registrations', 'uid')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('invoice_number', 100)->nullable();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->string('method', 50)->nullable(); // transfer, midtrans, cash
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_proof')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
