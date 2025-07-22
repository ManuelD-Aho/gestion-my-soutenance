<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id(); // ID technique
            $table->string('code', 50)->unique()->comment('Code unique de l\'action (ex: LOGIN_SUCCESS)');
            $table->string('label', 100)->comment('Libellé de l\'action');
            $table->string('category', 50)->nullable()->comment('Catégorie de l\'action (ex: Sécurité, Workflow)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};