<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique()->comment('ID métier du rapport (ex: RAP-2025-0001)');
            $table->string('title');
            $table->string('theme')->nullable();
            $table->text('abstract')->nullable();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('restrict');
            $table->string('status', 50)->default('draft')->comment('Statut du rapport (Enum: Brouillon, Soumis, Validé, etc.)');
            $table->integer('page_count')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->timestamp('last_modified_date')->nullable();
            $table->integer('version')->default(1)->comment('Version pour le verrouillage optimiste');
            $table->foreignId('report_template_id')->nullable()->constrained('report_templates')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
