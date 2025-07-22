<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Libellé du statut de pénalité (ex: Due, Réglée)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_statuses');
    }
};
