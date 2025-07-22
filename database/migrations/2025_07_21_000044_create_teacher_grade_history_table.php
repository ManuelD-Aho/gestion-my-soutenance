<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_grade_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('restrict');
            $table->date('acquisition_date');
            $table->timestamps();
            $table->unique(['teacher_id', 'grade_id', 'acquisition_date'], 'teacher_grade_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('teacher_grade_history');
    }
};