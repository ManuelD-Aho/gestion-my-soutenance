<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommissionSessionModeEnum;
use App\Enums\CommissionSessionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'session_id', // ID métier généré
        'name',
        'start_date',
        'end_date_planned',
        'president_teacher_id',
        'mode',
        'status',
        'required_voters_count',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date_planned' => 'datetime',
        'mode' => CommissionSessionModeEnum::class,
        'status' => CommissionSessionStatusEnum::class,
    ];

    public function president(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'president_teacher_id');
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'commission_session_report');
    }

    public function pvs(): HasMany
    {
        return $this->hasMany(Pv::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'commission_session_teacher'); // Table pivot pour les membres
    }
}
