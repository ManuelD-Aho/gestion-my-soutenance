<?php

    namespace App\Models;

    use App\Enums\ReportStatusEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\MorphMany;

    class Report extends Model
    {
        use HasFactory;

        protected $fillable = [
            'report_id', 'title', 'theme', 'abstract', 'student_id',
            'academic_year_id', 'status', 'page_count', 'submission_date', 'last_modified_date',
        ];

        protected $casts = [
            'submission_date' => 'datetime',
            'last_modified_date' => 'datetime',
            'status' => ReportStatusEnum::class,
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

        public function sections(): HasMany
        {
            return $this->hasMany(ReportSection::class);
        }

        public function conformityCheckDetails(): HasMany
        {
            return $this->hasMany(ConformityCheckDetail::class); // Assurez-vous de créer ce modèle
        }

        public function commissionSessions(): BelongsToMany
        {
            return $this->belongsToMany(CommissionSession::class, 'commission_session_report');
        }

        public function votes(): HasMany
        {
            return $this->hasMany(Vote::class);
        }

        public function pvs(): HasMany
        {
            return $this->hasMany(Pv::class);
        }

        public function auditLogs(): MorphMany
        {
            return $this->morphMany(AuditLog::class, 'auditable');
        }
    }