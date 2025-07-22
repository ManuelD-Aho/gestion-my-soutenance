<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ue_id')->constrained('ues')->onDelete('cascade');
            $table->string('name', 255)->comment('Nom de l\'Élément Constitutif d\'UE');
            $table->float('credits')->comment('Nombre de crédits ECTS');
            $table->timestamps();
            $table->unique(['ue_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecues');
    }
};
