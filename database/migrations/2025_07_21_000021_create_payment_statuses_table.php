<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Libellé du statut de paiement (ex: Payé, En attente)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_statuses');
    }
};
