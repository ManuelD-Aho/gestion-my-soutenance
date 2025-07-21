<?php

namespace App\Models;

use App\Enums\PvApprovalDecisionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PvApproval extends Model
{
    use HasFactory;

    protected $table = 'pv_approvals'; // Renommé pour cohérence
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'pv_id',
        'teacher_id', // Qui a approuvé
        'pv_approval_decision_id', // Enum PvApprovalDecisionEnum
        'validation_date',
        'comment',
    ];

    protected $casts = [
        'validation_date' => 'datetime',
        'pv_approval_decision_id' => PvApprovalDecisionEnum::class,
    ];

    public function pv(): BelongsTo
    {
        return $this->belongsTo(Pv::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(PvApprovalDecision::class, 'pv_approval_decision_id');
    }
}