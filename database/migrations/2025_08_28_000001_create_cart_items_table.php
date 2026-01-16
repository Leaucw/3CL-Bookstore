<?php
/**
 * Author: Chai Hao Lun
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id(); // CartItemID
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('quantity');
            $t->dateTime('added_at');
            $t->timestamps();
            $t->unique(['user_id','book_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cart_items');
    }
};
