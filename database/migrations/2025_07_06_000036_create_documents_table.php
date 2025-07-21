<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->string('document_id', 50)->unique()->comment('ID métier du document (ex: DOC-2025-0001)');
                $table->foreignId('document_type_id')->constrained('document_types')->onDelete('restrict');
                $table->string('file_path', 512)->comment('Chemin relatif du fichier stocké');
                $table->timestamp('generation_date')->useCurrent();
                $table->integer('version')->default(1);
                $table->morphs('related_entity'); // Polymorphique: peut être lié à un étudiant, un rapport, etc.
                $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // Qui a généré
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('documents');
        }
    };