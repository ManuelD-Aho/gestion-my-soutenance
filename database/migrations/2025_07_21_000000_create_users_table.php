<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée par Laravel
            $table->string('user_id', 50)->unique()->nullable()->comment('Identifiant métier unique de l\'utilisateur (ex: SYS-2025-0001)');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status', 50)->default('pending_validation')->comment('Statut du compte (actif, inactif, bloqué, en_attente_validation, archive)');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable(); // Pour Jetstream Teams
            $table->string('profile_photo_path', 2048)->nullable(); // Pour Jetstream Profile Photos
            $table->text('two_factor_secret')->nullable(); // Pour Jetstream 2FA
            $table->text('two_factor_recovery_codes')->nullable(); // Pour Jetstream 2FA
            $table->timestamp('two_factor_confirmed_at')->nullable(); // Pour Jetstream 2FA
            $table->string('email_verification_token', 64)->unique()->nullable()->comment('Jeton pour la vérification d\'email');
            $table->timestamp('email_verification_token_expires_at')->nullable()->comment('Date d\'expiration du jeton de vérification d\'email');
            $table->integer('failed_login_attempts')->default(0)->comment('Nombre de tentatives de connexion échouées');
            $table->timestamp('account_locked_until')->nullable()->comment('Date jusqu\'à laquelle le compte est bloqué');
            $table->timestamp('last_activity_at')->nullable()->comment('Date de la dernière activité de l\'utilisateur');
            $table->timestamps(); // created_at et updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
