<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('academic_decisions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique()->comment('Libellé de la décision académique (ex: Admis, Ajourné)');
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('academic_decisions');
        }
    };