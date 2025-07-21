<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('reclamations', function (Blueprint $table) {
                $table->id();
                $table->string('reclamation_id', 50)->unique()->comment('ID métier de la réclamation (ex: RECLA-2025-0001)');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->string('subject');
                $table->text('description');
                $table->timestamp('submission_date')->useCurrent();
                $table->string('status', 50)->default('open')->comment('Statut de la réclamation (Ouverte, En cours, Résolue)');
                $table->text('response')->nullable();
                $table->timestamp('response_date')->nullable();
                $table->foreignId('admin_staff_id')->nullable()->constrained('administrative_staff')->onDelete('set null'); // Qui a traité
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('reclamations');
        }
    };