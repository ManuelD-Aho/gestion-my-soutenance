<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Speciality extends Model
    {
        use HasFactory;

        protected $fillable = ['name', 'responsible_teacher_id'];

        // Relations
        public function responsibleTeacher(): BelongsTo
        {
            return $this->belongsTo(Teacher::class, 'responsible_teacher_id');
        }

        public function students(): HasMany
        {
            return $this->hasMany(Student::class); // Si un étudiant est directement lié à une spécialité
        }
    }