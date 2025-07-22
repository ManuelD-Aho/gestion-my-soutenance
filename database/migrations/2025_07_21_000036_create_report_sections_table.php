<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->string('title', 255);
            $table->longText('content')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->unique(['report_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_sections');
    }
};
