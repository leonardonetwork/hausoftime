<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('early_access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_prefix', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('country', 100)->nullable();
            $table->enum('type', ['buyer', 'seller'])->default('buyer');
            // Seller-specific fields
            $table->json('watch_brands')->nullable();       // brands they sell
            $table->integer('watch_count')->nullable();      // how many watches in inventory
            $table->string('estimated_value', 50)->nullable(); // estimated inventory value
            $table->text('additional_info')->nullable();
            // Status tracking
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('locale', 5)->default('es');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_access_requests');
    }
};
