<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pv_id')->constrained('pvs')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('pv_approval_decision_id')->constrained('pv_approval_decisions')->onDelete('restrict');
            $table->timestamp('validation_date')->useCurrent();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['pv_id', 'teacher_id'], 'unique_pv_approval');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_approvals');
    }
};
