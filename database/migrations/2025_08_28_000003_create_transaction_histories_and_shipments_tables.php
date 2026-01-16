<?php
/**
 * Author: Chai Hao Lun
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transaction_histories', function (Blueprint $t) {
            $t->id(); // TransactionID
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->dateTime('transaction_date');
            $t->decimal('amount', 12, 2);
            $t->enum('transaction_type', ['Payment','Refund']);
            $t->timestamps();
            $t->index(['order_id','transaction_date']);
        });

        Schema::create('shipments', function (Blueprint $t) {
            $t->id(); // ShipmentID
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->text('shipping_address');
            $t->dateTime('shipped_date')->nullable();
            $t->dateTime('delivery_date')->nullable();
            $t->timestamps();
            $t->unique('order_id'); // one shipment per order (simplified)
        });
    }
    public function down(): void {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('transaction_histories');
    }
};
