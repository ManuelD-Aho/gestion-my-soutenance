<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_parameters', function (Blueprint $table) {
            $table->string('key', 100)->primary()->comment('Clé unique du paramètre (ex: MAX_LOGIN_ATTEMPTS)');
            $table->text('value')->nullable()->comment('Valeur du paramètre');
            $table->text('description')->nullable();
            $table->string('type', 50)->default('string')->comment('Type de la valeur (string, integer, boolean, json)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_parameters');
    }
};
