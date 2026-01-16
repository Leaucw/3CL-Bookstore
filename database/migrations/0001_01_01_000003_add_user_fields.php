<?php

/**
 * Author: Chiang Jong Sheng
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('username')->unique()->after('id');
            $t->enum('role', ['manager','staff','customer'])->default('customer')->index();
            $t->string('phone')->nullable();
            $t->string('address')->nullable();
            $t->string('avatar_path')->nullable();
            $t->unsignedInteger('points')->default(0);     // member point (customers)
            $t->softDeletes();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['username','role','phone','address','avatar_path','points','deleted_at']);
        });
    }
};
