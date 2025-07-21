<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Teacher extends Model
    {
        use HasFactory;

        protected $fillable = [
            'teacher_id', 'first_name', 'last_name', 'professional_phone', 'professional_email',
            'user_id', 'date_of_birth', 'place_of_birth', 'country_of_birth', 'nationality', 'gender',
            'address', 'city', 'postal_code', 'personal_phone', 'personal_secondary_email',
        ];

        protected $casts = [
            'date_of_birth' => 'date',
            'gender' => \App\Enums\GenderEnum::class, // Assurez-vous de créer cet Enum si utilisé
        ];

        // Relations
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function presidentOfSessions(): HasMany
        {
            return $this->hasMany(CommissionSession::class, 'president_teacher_id');
        }

        public function votes(): HasMany
        {
            return $this->hasMany(Vote::class);
        }

        public function gradeHistory(): HasMany
        {
            return $this->hasMany(TeacherGradeHistory::class);
        }

        public function functionHistory(): HasMany
        {
            return $this->hasMany(TeacherFunctionHistory::class);
        }

        public function specialities(): HasMany
        {
            return $this->hasMany(Speciality::class, 'responsible_teacher_id');
        }
    }