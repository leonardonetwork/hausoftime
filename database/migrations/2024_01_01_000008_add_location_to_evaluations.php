<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('watch_evaluations', function (Blueprint $table) {
            $table->string('phone_prefix', 10)->nullable()->after('email');
            $table->string('phone', 30)->nullable()->after('phone_prefix');
            $table->string('country', 100)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('country');
        });
    }
    public function down(): void {
        Schema::table('watch_evaluations', function (Blueprint $table) {
            $table->dropColumn(['phone_prefix','phone','country','city']);
        });
    }
};
