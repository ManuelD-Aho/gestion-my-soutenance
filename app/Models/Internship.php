<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Internship extends Model
    {
        use HasFactory;

        protected $fillable = [
            'student_id', 'company_id', 'start_date', 'end_date',
            'subject', 'company_tutor_name', 'is_validated',
        ];

        protected $casts = [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_validated' => 'boolean',
        ];

        // Relations
        public function student(): BelongsTo
        {
            return $this->belongsTo(Student::class);
        }

        public function company(): BelongsTo
        {
            return $this->belongsTo(Company::class);
        }
    }