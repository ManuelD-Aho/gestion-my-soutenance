<?php

namespace App\Models;

use App\Enums\PvStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pv extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'pv_id', // ID métier généré
        'commission_session_id',
        'report_id', // Peut être null si c'est un PV de session global
        'type', // 'session', 'report_specific'
        'content',
        'author_user_id', // Qui a rédigé le PV
        'status', // Enum PvStatusEnum
        'approval_deadline', // Date limite pour les approbations
        'version', // Pour le versionnage du PV
    ];

    protected $casts = [
        'approval_deadline' => 'datetime',
        'status' => PvStatusEnum::class,
        'version' => 'int',
    ];

    public function commissionSession(): BelongsTo
    {
        return $this->belongsTo(CommissionSession::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PvApproval::class);
    }
}