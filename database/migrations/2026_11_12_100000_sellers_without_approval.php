<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** I venditori non si approvano più: si approvano solo i relojes. */
    public function up(): void
    {
        DB::table('users')->where('role', 'seller')->where('status', 'pending')->update(['status' => 'approved', 'updated_at' => now()]);
        DB::table('early_access_requests')->where('type', 'seller')->where('status', 'pending')
            ->update(['status' => 'approved', 'approved_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void {}
};
