<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->json('registration_uids')->nullable();
            $table->string('payment_id')->nullable();
            $table->foreign('payment_id')
                  ->references('uid')
                  ->on('payments')
                  ->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->decimal('tax', 8, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])
                  ->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
?>
