<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_session_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_session_id')->constrained('commission_sessions')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['commission_session_id', 'report_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_session_report');
    }
};
