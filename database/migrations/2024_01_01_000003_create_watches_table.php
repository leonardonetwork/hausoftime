<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('brand');
            $table->string('model');
            $table->string('reference')->nullable();
            $table->year('manufacture_year')->nullable();
            $table->enum('condition', ['mint', 'excellent', 'very_good', 'good', 'fair'])->default('excellent');
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('EUR');
            // Box & Papers
            $table->enum('box_papers', ['none', 'box_only', 'papers_only', 'full_set'])->default('none');
            $table->json('documentation_files')->nullable(); // PDF files paths
            $table->json('images')->nullable();               // Image paths
            // Status workflow
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'sold'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watches');
    }
};
