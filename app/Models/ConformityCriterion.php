<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConformityCriterion extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'label',
        'description',
        'is_active',
        'type', // 'MANUAL', 'AUTOMATIC'
        'version', // Pour le versionnage des critères
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'int',
    ];

    public function conformityCheckDetails(): HasMany
    {
        return $this->hasMany(ConformityCheckDetail::class);
    }
}