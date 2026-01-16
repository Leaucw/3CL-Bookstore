<?php
/**
 * Author: Chai Hao Lun
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $t) {
            $t->id(); // OrderID
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->dateTime('order_date');
            $t->enum('status', ['Processing','Shipped','Arrived','Completed','Cancelled'])->default('Processing');
            $t->decimal('subtotal_amount', 12, 2);
            $t->decimal('discount_amount', 12, 2)->default(0);
            $t->decimal('shipping_amount', 12, 2)->default(0);
            $t->decimal('total_amount', 12, 2);
            $t->string('payment_method');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['user_id','status','order_date']);
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('books')->restrictOnDelete();
            $t->unsignedInteger('quantity');
            $t->decimal('unit_price', 12, 2);
            $t->timestamps();
            $t->unique(['order_id','book_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
