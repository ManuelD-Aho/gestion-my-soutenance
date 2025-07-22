<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->string('penalty_id', 50)->unique()->comment('ID métier de la pénalité (ex: PEN-2025-0001)');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('restrict');
            $table->string('type', 50)->comment('Type de pénalité (Financière, Administrative)');
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 50)->default('due')->comment('Statut de la pénalité (Due, Paid, Waived)');
            $table->timestamp('creation_date')->useCurrent();
            $table->timestamp('resolution_date')->nullable();
            $table->foreignId('admin_staff_id')->nullable()->constrained('administrative_staff')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
