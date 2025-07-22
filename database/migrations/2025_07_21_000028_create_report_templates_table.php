<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_id', 50)->unique()->comment('ID métier du modèle (ex: TPL-2025-0001)');
            $table->string('name', 255)->comment('Nom du modèle (ex: Modèle Standard MIAGE)');
            $table->text('description')->nullable();
            $table->string('version', 10)->default('1.0');
            $table->string('status', 50)->default('draft')->comment('Statut du modèle (Brouillon, Publié, Archivé)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
