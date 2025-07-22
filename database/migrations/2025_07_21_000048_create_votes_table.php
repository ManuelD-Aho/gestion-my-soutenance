<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('vote_id', 50)->unique()->comment('ID métier du vote (ex: VOTE-2025-0001)');
            $table->foreignId('commission_session_id')->constrained('commission_sessions')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('vote_decision_id')->constrained('vote_decisions')->onDelete('restrict');
            $table->text('comment')->nullable();
            $table->timestamp('vote_date')->useCurrent();
            $table->integer('vote_round')->default(1)->comment('Numéro du tour de vote');
            $table->string('status', 50)->default('ACTIVE')->comment('Statut du vote (ACTIVE, CANCELLED)');
            $table->timestamps();
            $table->unique(['commission_session_id', 'report_id', 'teacher_id', 'vote_round'], 'unique_vote_per_round');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
