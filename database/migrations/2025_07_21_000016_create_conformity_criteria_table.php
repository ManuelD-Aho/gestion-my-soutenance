<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conformity_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('label', 255)->comment('Libellé du critère (ex: Respect de la page de garde)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('type', 50)->default('MANUAL')->comment('Type de critère (MANUAL, AUTOMATIC)');
            $table->integer('version')->default(1)->comment('Version du critère');
            $table->string('code', 50)->unique()->nullable()->comment('Code unique pour les critères automatiques (ex: DEADLINE_RESPECTED)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conformity_criteria');
    }
};
