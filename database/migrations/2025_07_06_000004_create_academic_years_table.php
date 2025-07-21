<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('academic_years', function (Blueprint $table) {
                $table->id();
                $table->string('label', 50)->unique()->comment('Libellé de l\'année académique (ex: 2024-2025)');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(false)->comment('Indique si c\'est l\'année académique courante');
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('academic_years');
        }
    };