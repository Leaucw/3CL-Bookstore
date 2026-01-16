<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('other'); // book_fair, webinar, etc.
            $table->string('delivery_mode')->default('onsite'); // online, onsite, hybrid
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->enum('visibility', ['public','private','targeted'])->default('public');
            $table->enum('status', ['draft','scheduled','live','completed','cancelled'])->default('draft');
            $table->integer('points_reward')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
