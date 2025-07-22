<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Libellé du niveau d\'étude (ex: Licence 3, Master 2)');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_levels');
    }
};
