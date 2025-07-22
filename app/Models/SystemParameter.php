<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemParameter extends Model
{
    use HasFactory;

    protected $primaryKey = 'key'; // Clé primaire est une chaîne de caractères

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'description',
        'type', // Ex: 'string', 'int', 'boolean', 'json'
    ];

    protected $casts = [
        // 'value' => 'string', // Sera casté dynamiquement par la méthode getValue()
    ];

    public static function getValue(string $key, $default = null)
    {
        $parameter = static::find($key);
        if (! $parameter) {
            return $default;
        }

        // Cast dynamique basé sur le champ 'type'
        return match ($parameter->type) {
            'int' => (int) $parameter->value,
            'boolean' => (bool) $parameter->value,
            'json' => json_decode($parameter->value, true),
            default => $parameter->value, // string par défaut
        };
    }
}
