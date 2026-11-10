<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->enum('type', ['buyer', 'seller', 'general'])->default('general');
            $table->string('locale', 5)->default('es');
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject_es');
            $table->string('subject_en');
            $table->longText('body_es');
            $table->longText('body_en');
            $table->enum('recipient_type', ['all', 'buyers', 'sellers', 'custom'])->default('all');
            $table->json('custom_emails')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent'])->default('draft');
            $table->integer('recipient_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('type', 20)->default('string'); // string, boolean, json, encrypted
            $table->timestamps();
        });

        Schema::create('watch_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone_prefix', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('brand');
            $table->string('model');
            $table->string('reference')->nullable();
            $table->year('manufacture_year')->nullable();
            $table->enum('condition', ['mint', 'excellent', 'very_good', 'good', 'fair'])->nullable();
            $table->enum('box_papers', ['none', 'box_only', 'papers_only', 'full_set'])->nullable();
            $table->json('ai_response')->nullable();
            $table->string('estimated_value_min', 50)->nullable();
            $table->string('estimated_value_max', 50)->nullable();
            $table->string('locale', 5)->default('es');
            $table->timestamps();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('watch_evaluations');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
