<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ecue extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'ue_id',
        'credits',
    ];

    protected $casts = [
        'credits' => 'float',
    ];

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }
}