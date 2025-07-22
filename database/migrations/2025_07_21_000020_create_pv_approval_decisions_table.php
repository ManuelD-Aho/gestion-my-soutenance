<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_approval_decisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Libellé de la décision d\'approbation de PV (ex: Approuvé, Modification Demandée)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_approval_decisions');
    }
};
