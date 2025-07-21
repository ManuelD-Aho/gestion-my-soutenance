<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fonction extends Model
{
    use HasFactory;

    protected $table = 'fonctions'; // Nom de la table en base de données
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'description',
    ];

    public function teacherFunctionHistory(): HasMany
    {
        return $this->hasMany(TeacherFunctionHistory::class, 'function_id');
    }
}