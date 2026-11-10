<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabella liste
        Schema::create('newsletter_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Aggiungi list_id agli iscritti
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->foreignId('list_id')->nullable()->constrained('newsletter_lists')->nullOnDelete()->after('id');
        });

        // Cambia unique da solo email a (email + list_id)
        // Prima rimuovi il vecchio unique sull'email
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['email', 'list_id']);
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropUnique(['email', 'list_id']);
            $table->dropForeign(['list_id']);
            $table->dropColumn('list_id');
            $table->unique('email');
        });
        Schema::dropIfExists('newsletter_lists');
    }
};
