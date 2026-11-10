<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watches', function (Blueprint $table) {
            // Ora casuale che il vendedor deve impostare sul reloj prima della foto
            $table->string('verification_time', 5)->nullable()->after('admin_notes');
            $table->string('verification_photo')->nullable()->after('verification_time');
            $table->timestamp('verification_at')->nullable()->after('verification_photo');
        });
    }

    public function down(): void
    {
        Schema::table('watches', function (Blueprint $table) {
            $table->dropColumn(['verification_time', 'verification_photo', 'verification_at']);
        });
    }
};
