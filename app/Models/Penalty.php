<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PenaltyStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penalty extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'penalty_id', // ID métier généré
        'student_id',
        'academic_year_id',
        'type', // 'Financière', 'Administrative'
        'amount',
        'reason',
        'status', // Enum PenaltyStatusEnum
        'creation_date',
        'resolution_date',
        'admin_staff_id', // Qui a appliqué/géré la pénalité
    ];

    protected $casts = [
        'amount' => 'float',
        'creation_date' => 'datetime',
        'resolution_date' => 'datetime',
        'status' => PenaltyStatusEnum::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function administrativeStaff(): BelongsTo
    {
        return $this->belongsTo(AdministrativeStaff::class, 'admin_staff_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PenaltyPayment::class);
    }
}
