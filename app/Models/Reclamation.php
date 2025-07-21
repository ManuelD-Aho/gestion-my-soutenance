<?php

    namespace App\Models;

    use App\Enums\ReclamationStatusEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Reclamation extends Model
    {
        use HasFactory;

        protected $fillable = [
            'reclamation_id', 'student_id', 'subject', 'description',
            'submission_date', 'status', 'response', 'response_date',
            'admin_staff_id',
        ];

        protected $casts = [
            'submission_date' => 'datetime',
            'response_date' => 'datetime',
            'status' => ReclamationStatusEnum::class,
        ];

        // Relations
        public function student(): BelongsTo
        {
            return $this->belongsTo(Student::class);
        }

        public function reclamationStatus(): BelongsTo
        {
            return $this->belongsTo(ReclamationStatus::class, 'status'); // Si le nom de la colonne est 'status'
        }

        public function administrativeStaff(): BelongsTo
        {
            return $this->belongsTo(AdministrativeStaff::class, 'admin_staff_id');
        }
    }