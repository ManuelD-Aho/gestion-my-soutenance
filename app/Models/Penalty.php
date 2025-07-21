<?php

    namespace App\Models;

    use App\Enums\PenaltyStatusEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Penalty extends Model
    {
        use HasFactory;

        protected $fillable = [
            'penalty_id', 'student_id', 'academic_year_id', 'type',
            'amount', 'reason', 'status', 'creation_date', 'resolution_date',
            'admin_staff_id',
        ];

        protected $casts = [
            'creation_date' => 'datetime',
            'resolution_date' => 'datetime',
            'status' => PenaltyStatusEnum::class,
        ];

        // Relations
        public function student(): BelongsTo
        {
            return $this->belongsTo(Student::class);
        }

        public function academicYear(): BelongsTo
        {
            return $this->belongsTo(AcademicYear::class);
        }

        public function penaltyStatus(): BelongsTo
        {
            return $this->belongsTo(PenaltyStatus::class, 'status'); // Si le nom de la colonne est 'status'
        }

        public function administrativeStaff(): BelongsTo
        {
            return $this->belongsTo(AdministrativeStaff::class, 'admin_staff_id');
        }
    }