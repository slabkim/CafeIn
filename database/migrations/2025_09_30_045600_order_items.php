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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_positive CHECK (quantity > 0)");
        DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_subtotal_non_negative CHECK (subtotal >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
