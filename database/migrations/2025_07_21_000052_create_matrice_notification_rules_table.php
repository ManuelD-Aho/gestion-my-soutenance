<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matrice_notification_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_id')->constrained('actions')->onDelete('cascade');
            $table->string('recipient_role_name', 100)->comment('Nom du rôle Spatie du destinataire');
            $table->string('channel', 50)->comment('Canal de notification (Interne, Email, Tous)');
            $table->string('mailable_class_name', 255)->nullable()->comment('Nom complet de la classe Mailable (si canal email)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['action_id', 'recipient_role_name', 'channel'], 'unique_notification_rule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matrice_notification_rules');
    }
};