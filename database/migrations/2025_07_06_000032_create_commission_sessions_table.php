<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('commission_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 50)->unique()->comment('ID métier de la session (ex: SESS-2025-0001)');
                $table->string('name', 255);
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date_planned')->nullable();
                $table->foreignId('president_teacher_id')->constrained('teachers')->onDelete('restrict');
                $table->string('mode', 50)->comment('Mode de la session (présentiel, en_ligne, hybride)');
                $table->string('status', 50)->default('planned')->comment('Statut de la session (planifiée, en_cours, cloturée)');
                $table->integer('required_voters_count')->nullable()->comment('Nombre de votants requis pour consensus');
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('commission_sessions');
        }
    };