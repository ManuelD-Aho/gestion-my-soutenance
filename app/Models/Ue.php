<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ue extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'credits',
    ];

    protected $casts = [
        'credits' => 'float',
    ];

    public function ecues(): HasMany
    {
        return $this->hasMany(Ecue::class);
    }
}
