<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConformityStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConformityCheckDetail extends Model
{
    use HasFactory;

    protected $table = 'conformity_check_details'; // Renommé pour cohérence

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'report_id',
        'conformity_criterion_id',
        'validation_status',
        'comment',
        'verification_date',
        'verified_by_user_id', // Qui a effectué la vérification
        'criterion_label', // Snapshot du libellé au moment de la vérification
        'criterion_description', // Snapshot de la description
        'criterion_version', // Version du critère au moment du snapshot
    ];

    protected $casts = [
        'validation_status' => ConformityStatusEnum::class,
        'verification_date' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(ConformityCriterion::class, 'conformity_criterion_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
