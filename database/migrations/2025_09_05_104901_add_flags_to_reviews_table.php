<?php
//Author: Leong Hui Hui
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->boolean('flagged')->default(false);
        $table->boolean('hidden')->default(false);
    });
}

public function down()
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->dropColumn(['flagged', 'hidden']);
    });
}
};
