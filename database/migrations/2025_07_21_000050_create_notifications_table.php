<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique()->comment('Code unique du template de notification (ex: ACCOUNT_ACTIVATED)');
            $table->string('subject', 255)->comment('Sujet par défaut de la notification');
            $table->longText('content')->comment('Contenu du template avec placeholders');
            $table->boolean('is_active')->default(true);
            $table->string('level', 50)->default('INFO')->comment('Niveau d\'urgence (INFO, WARNING, CRITICAL)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};