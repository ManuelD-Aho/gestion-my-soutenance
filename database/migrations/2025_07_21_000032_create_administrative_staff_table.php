<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrative_staff', function (Blueprint $table) {
            $table->id();
            $table->string('staff_id', 50)->unique()->comment('Numéro personnel administratif (ID métier)');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('professional_phone', 20)->nullable();
            $table->string('professional_email', 255)->unique()->nullable();
            $table->date('service_assignment_date')->nullable();
            $table->text('key_responsibilities')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->string('country_of_birth', 50)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->enum('gender', ['Masculin', 'Féminin', 'Autre'])->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('personal_phone', 20)->nullable();
            $table->string('personal_secondary_email', 255)->nullable();
            $table->boolean('is_active')->default(true)->comment('Indique si le profil administratif est actif');
            $table->date('end_date')->nullable()->comment('Date de fin d\'activité ou d\'archivage du profil');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrative_staff');
    }
};