<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watch_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name_es');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('watch_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_es');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_categories');
        Schema::dropIfExists('watch_brands');
    }
};
