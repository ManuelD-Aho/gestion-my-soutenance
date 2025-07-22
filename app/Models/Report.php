<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Report extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'report_id', // ID métier généré
        'title',
        'theme',
        'abstract',
        'student_id',
        'academic_year_id',
        'status', // Enum ReportStatusEnum
        'page_count',
        'submission_date',
        'last_modified_date',
        'version', // Pour le verrouillage optimiste
        'report_template_id', // Si basé sur un modèle
    ];

    protected $casts = [
        'page_count' => 'int',
        'submission_date' => 'datetime',
        'last_modified_date' => 'datetime',
        'status' => ReportStatusEnum::class,
        'version' => 'int',
    ];

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
        return $this->hasMany(ConformityCheckDetail::class);
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

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }
}
