<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('specialities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique()->comment('Libellé de la spécialité (ex: MIAGE, Cybersécurité)');
                $table->foreignId('responsible_teacher_id')->nullable()->constrained('teachers')->onDelete('set null'); // Responsable de spécialité
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('specialities');
        }
    };