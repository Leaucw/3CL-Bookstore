<?php
//Leau Chee Way
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique(); 
            $t->timestamps();
        });

        Schema::create('book_tag', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->unique(['book_id','tag_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('book_tag');
        Schema::dropIfExists('tags');
    }
};
