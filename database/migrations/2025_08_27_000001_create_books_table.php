<?php
//Leau Chee Way
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('books', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('author');
            $t->string('isbn')->unique();
            $t->string('genre')->nullable();
            $t->text('description')->nullable();
            $t->unsignedInteger('stock')->default(0);
            $t->decimal('price', 10, 2);
            $t->json('metadata')->nullable();
            $t->string('cover_image_path')->nullable(); // image path
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('book_category', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->unique(['book_id','category_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('books');
    }
};
