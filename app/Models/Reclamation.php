<?php

namespace App\Models;

use App\Enums\ReclamationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reclamation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'reclamation_id', // ID métier généré
        'student_id',
        'subject',
        'description',
        'submission_date',
        'status', // Enum ReclamationStatusEnum
        'response',
        'response_date',
        'admin_staff_id', // Qui a traité la réclamation
        'reclaimable_type', // Relation polymorphique pour la contestation (ex: une Penalty)
        'reclaimable_id',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'response_date' => 'datetime',
        'status' => ReclamationStatusEnum::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reclamationStatus(): BelongsTo
    {
        return $this->belongsTo(ReclamationStatus::class, 'status');
    }

    public function administrativeStaff(): BelongsTo
    {
        return $this->belongsTo(AdministrativeStaff::class, 'admin_staff_id');
    }

    public function reclaimable(): MorphTo
    {
        return $this->morphTo();
    }
}