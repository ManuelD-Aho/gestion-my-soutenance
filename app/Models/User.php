<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserAccountStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, HasRoles, HasTeams, Impersonate, Notifiable, TwoFactorAuthenticatable;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id', // ID métier généré
        'name',
        'email',
        'password',
        'status', // Enum UserAccountStatusEnum
        'email_verified_at',
        'email_verification_token', // Pour le mécanisme de vérification d'email
        'email_verification_token_expires_at',
        'failed_login_attempts', // Pour le blocage de compte après tentatives échouées
        'account_locked_until', // Date jusqu'à laquelle le compte est bloqué
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verification_token', // Masquer le token de vérification
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => UserAccountStatusEnum::class,
        'email_verification_token_expires_at' => 'datetime',
        'failed_login_attempts' => 'int',
        'account_locked_until' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    public function canImpersonate(): bool
    {
        return $this->hasRole('Admin');
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('Admin');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function administrativeStaff(): HasOne
    {
        return $this->hasOne(AdministrativeStaff::class, 'user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'generated_by_user_id');
    }
}
