<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class TeacherGradeHistory extends Model
    {
        use HasFactory;

        protected $table = 'teacher_grade_history'; // Nom de la table pivot

        protected $fillable = ['teacher_id', 'grade_id', 'acquisition_date'];

        protected $casts = [
            'acquisition_date' => 'date',
        ];

        // Relations
        public function teacher(): BelongsTo
        {
            return $this->belongsTo(Teacher::class);
        }

        public function grade(): BelongsTo
        {
            return $this->belongsTo(Grade::class);
        }
    }