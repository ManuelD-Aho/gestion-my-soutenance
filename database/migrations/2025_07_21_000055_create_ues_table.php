<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique()->comment('Nom de l\'Unité d\'Enseignement');
            $table->float('credits')->comment('Nombre de crédits ECTS');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ues');
    }
};
