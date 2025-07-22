<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenaltyStatus extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', // Ex: 'Due', 'Réglée', 'Annulée'
    ];

    /**
     * Obtenir les pénalités associées à ce statut.
     */
    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class, 'status'); // Assumer que la colonne de statut dans 'penalties' est 'status'
    }
}
