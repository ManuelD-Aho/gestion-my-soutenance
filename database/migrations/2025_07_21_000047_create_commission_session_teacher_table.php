<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_session_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_session_id')->constrained('commission_sessions')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('role_in_session', 50)->nullable()->comment('Rôle spécifique dans cette session (ex: Rapporteur)');
            $table->timestamps();
            $table->unique(['commission_session_id', 'teacher_id'], 'unique_session_teacher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_session_teacher');
    }
};