<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use Impersonate;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    // Custom primary key
    protected $primaryKey = 'id'; // Laravel default is 'id'
    // protected $primaryKey = 'user_id'; // If you want to use user_id as PK
    // public $incrementing = false; // If primary key is not auto-incrementing
    // protected $keyType = 'string'; // If primary key is string

    public function canImpersonate(): bool
    {
        return $this->hasRole('Admin');
    }

    public function canBeImpersonated(): bool
    {
        return !$this->hasRole('Admin');
    }
}
