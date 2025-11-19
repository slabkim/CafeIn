<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->enum('method', ['qris', 'gopay', 'ovo', 'dana', 'cash']);
            $table->string('transaction_id')->nullable()->unique(); // dari API payment gateway / provider reference
            $table->string('provider')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_amount_non_negative CHECK (amount >= 0)");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_fee_non_negative CHECK (fee >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
