<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'log_id', // ID métier généré
        'user_id',
        'action_id',
        'action_date',
        'ip_address',
        'user_agent',
        'auditable_type',
        'auditable_id',
        'details',
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}