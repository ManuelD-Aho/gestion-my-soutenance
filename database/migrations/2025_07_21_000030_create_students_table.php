<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_card_number', 50)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email_contact_personnel', 255)->unique()->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->string('country_of_birth', 50)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->enum('gender', ['Masculin', 'Féminin', 'Autre'])->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('secondary_email', 255)->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};