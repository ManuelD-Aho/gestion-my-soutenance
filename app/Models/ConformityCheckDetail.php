<?php
namespace App\Models;
use App\Enums\ConformityStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConformityCheckDetail extends Model
{
    use HasFactory;
    protected $table = 'conformity_rapport_details'; // Nom de la table
    protected $fillable = ['id_conformite_detail', 'report_id', 'conformity_criterion_id', 'validation_status', 'comment', 'verification_date'];
    protected $casts = [
        'validation_status' => ConformityStatusEnum::class,
        'verification_date' => 'datetime',
    ];
    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function criterion(): BelongsTo { return $this->belongsTo(ConformityCriterion::class, 'conformity_criterion_id'); }
}