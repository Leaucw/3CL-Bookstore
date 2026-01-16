<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating'); // 1..5
            $t->text('content')->nullable();
            $t->timestamps();
             $t->unique(['book_id','user_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};
