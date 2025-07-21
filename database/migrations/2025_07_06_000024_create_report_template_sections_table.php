<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('report_template_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_template_id')->constrained('report_templates')->onDelete('cascade');
                $table->string('title', 255)->comment('Titre de la section (ex: Introduction, Conclusion)');
                $table->longText('default_content')->nullable()->comment('Contenu par défaut de la section');
                $table->integer('order')->default(0)->comment('Ordre d\'affichage de la section');
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('report_template_sections');
        }
    };