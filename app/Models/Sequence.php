<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use HasFactory;

    protected $primaryKey = ['name', 'year']; // Clé primaire composite
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'year',
        'value',
    ];

    protected $casts = [
        'year' => 'int',
        'value' => 'int',
    ];
}