<?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('internships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->text('subject')->nullable();
                $table->string('company_tutor_name', 100)->nullable();
                $table->boolean('is_validated')->default(false)->comment('Indique si le stage a été validé par le RS');
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('internships');
        }
    };