<?php

namespace App\Models;

use App\Enums\AcademicYearStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $primaryKey = 'id'; // Assumer que 'id' est la PK auto-incrémentée par défaut
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'academic_year_id', // ID métier généré
        'label',
        'start_date',
        'end_date',
        'is_active',
        'status',
        'report_submission_deadline', // Date limite de soumission des rapports
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'status' => AcademicYearStatusEnum::class,
        'report_submission_deadline' => 'datetime',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}