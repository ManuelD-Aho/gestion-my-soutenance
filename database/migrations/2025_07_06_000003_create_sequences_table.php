<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('sequences', function (Blueprint $table) {
                $table->string('name', 50)->comment('Nom de la séquence (ex: ETU, RAP)');
                $table->year('year')->comment('Année de la séquence');
                $table->unsignedInteger('value')->default(0)->comment('Valeur actuelle du compteur');
                $table->primary(['name', 'year']); // Clé primaire composite
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('sequences');
        }
    };