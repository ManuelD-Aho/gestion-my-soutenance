<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('pvs', function (Blueprint $table) {
                $table->id();
                $table->string('pv_id', 50)->unique()->comment('ID métier du PV (ex: PV-2025-0001)');
                $table->foreignId('commission_session_id')->constrained('commission_sessions')->onDelete('cascade');
                $table->foreignId('report_id')->nullable()->constrained('reports')->onDelete('set null'); // Pour PV individuel
                $table->string('type', 50)->default('session')->comment('Type de PV (session, individuel)');
                $table->longText('content')->comment('Contenu textuel du PV');
                $table->foreignId('author_user_id')->constrained('users')->onDelete('restrict'); // Qui a rédigé
                $table->string('status', 50)->default('draft')->comment('Statut du PV (Brouillon, En attente approbation, Validé)');
                $table->timestamp('approval_deadline')->nullable();
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('pvs');
        }
    };