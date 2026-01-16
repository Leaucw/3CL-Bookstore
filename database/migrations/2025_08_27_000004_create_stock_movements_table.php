<?php
//Leau Chee Way
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('type', ['restock','sale','adjustment']);
            $t->integer('quantity_change'); 
            $t->string('reason')->nullable();
            $t->timestamps();
            $t->index(['book_id','created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('stock_movements'); }
};
