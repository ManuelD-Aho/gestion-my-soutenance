<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('teachers', function (Blueprint $table) {
                $table->id();
                $table->string('teacher_id', 50)->unique()->comment('Numéro enseignant (ID métier)');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('professional_phone', 20)->nullable();
                $table->string('professional_email', 255)->unique()->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Clé étrangère vers le compte utilisateur (nullable)
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
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('teachers');
        }
    };