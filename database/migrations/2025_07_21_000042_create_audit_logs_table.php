<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_id', 50)->unique()->comment('ID métier du log (ex: LOG-2025-0001)');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('action_id')->constrained('actions')->onDelete('restrict');
            $table->timestamp('action_date')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->morphs('auditable');
            $table->json('details')->nullable()->comment('Détails supplémentaires de l\'action (ex: anciennes/nouvelles valeurs)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
