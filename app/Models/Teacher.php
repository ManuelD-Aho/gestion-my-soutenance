<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'teacher_id', // ID métier généré
        'first_name',
        'last_name',
        'professional_phone',
        'professional_email',
        'user_id',
        'date_of_birth',
        'place_of_birth',
        'country_of_birth',
        'nationality',
        'gender',
        'address',
        'city',
        'postal_code',
        'personal_phone',
        'personal_secondary_email',
        'is_active', // Ajout pour gestion de l'historique
        'end_date', // Date de fin d'activité pour l'archivage
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'gender' => GenderEnum::class,
        'is_active' => 'boolean',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function presidentOfSessions(): HasMany
    {
        return $this->hasMany(CommissionSession::class, 'president_teacher_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function gradeHistory(): HasMany
    {
        return $this->hasMany(TeacherGradeHistory::class, 'teacher_id');
    }

    public function functionHistory(): HasMany
    {
        return $this->hasMany(TeacherFunctionHistory::class, 'teacher_id');
    }

    public function specialities(): HasMany
    {
        return $this->hasMany(Speciality::class, 'responsible_teacher_id');
    }

    public function commissionSessions(): BelongsToMany
    {
        return $this->belongsToMany(CommissionSession::class, 'commission_session_teacher');
    }
}
