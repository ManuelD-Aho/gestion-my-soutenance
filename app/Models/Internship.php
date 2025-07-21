<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Internship extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'student_id',
        'company_id',
        'start_date',
        'end_date',
        'subject',
        'company_tutor_name',
        'is_validated',
        'validation_date', // Date de validation du stage
        'validated_by_user_id', // Qui a validé
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_validated' => 'boolean',
        'validation_date' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }
}