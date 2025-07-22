<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_function_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('function_id')->constrained('fonctions')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'function_id', 'start_date'], 'teacher_function_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_function_history');
    }
};
