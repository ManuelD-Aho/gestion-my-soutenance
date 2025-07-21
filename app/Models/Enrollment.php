<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Enrollment extends Model
    {
        use HasFactory;

        protected $fillable = [
            'student_id', 'study_level_id', 'academic_year_id', 'enrollment_amount',
            'enrollment_date', 'payment_status_id', 'payment_date', 'payment_receipt_number',
            'academic_decision_id',
        ];

        protected $casts = [
            'enrollment_date' => 'datetime',
            'payment_date' => 'datetime',
        ];

        // Relations
        public function student(): BelongsTo
        {
            return $this->belongsTo(Student::class);
        }

        public function studyLevel(): BelongsTo
        {
            return $this->belongsTo(StudyLevel::class);
        }

        public function academicYear(): BelongsTo
        {
            return $this->belongsTo(AcademicYear::class);
        }

        public function paymentStatus(): BelongsTo
        {
            return $this->belongsTo(PaymentStatus::class);
        }

        public function academicDecision(): BelongsTo
        {
            return $this->belongsTo(AcademicDecision::class);
        }
    }