<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jury_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Libellé du rôle dans le jury (ex: Président, Rapporteur)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jury_roles');
    }
};