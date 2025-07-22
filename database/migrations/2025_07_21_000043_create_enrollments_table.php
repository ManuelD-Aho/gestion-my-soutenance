<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('study_level_id')->constrained('study_levels')->onDelete('restrict');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('restrict');
            $table->decimal('enrollment_amount', 10, 2);
            $table->timestamp('enrollment_date')->useCurrent();
            $table->foreignId('payment_status_id')->constrained('payment_statuses')->onDelete('restrict');
            $table->timestamp('payment_date')->nullable();
            $table->string('payment_receipt_number', 50)->unique()->nullable();
            $table->foreignId('academic_decision_id')->nullable()->constrained('academic_decisions')->onDelete('set null');
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
