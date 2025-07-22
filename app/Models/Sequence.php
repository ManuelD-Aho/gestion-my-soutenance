<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'year', 'value'];

    // Clé primaire composite
    protected $primaryKey = ['name', 'year'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'year' => 'int',
        'value' => 'int',
    ];

    // Surcharge de la méthode setKeysForSaveQuery pour gérer les clés composites
    // Ceci est souvent nécessaire pour les modèles avec clés composites
    protected function setKeysForSaveQuery($query): Builder
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQueryComposite($keyName));
        }

        return $query;
    }

    // Méthode d'aide pour récupérer la valeur d'une partie de la clé composite
    protected function getKeyForSaveQueryComposite($keyName)
    {
        return $this->original[$keyName] ?? $this->getAttribute($keyName);
    }
}
