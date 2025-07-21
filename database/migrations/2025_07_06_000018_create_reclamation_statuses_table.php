<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('reclamation_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique()->comment('Libellé du statut de réclamation (ex: Ouverte, Résolue)');
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('reclamation_statuses');
        }
    };