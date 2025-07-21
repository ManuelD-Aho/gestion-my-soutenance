<?php

    namespace App\Models;

    use App\Enums\UserAccountStatusEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Laravel\Fortify\TwoFactorAuthenticatable;
    use Laravel\Jetstream\HasProfilePhoto;
    use Laravel\Jetstream\HasTeams;
    use Laravel\Sanctum\HasApiTokens;
    use Spatie\Permission\Traits\HasRoles;
    use Lab404\Impersonate\Models\Impersonate;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'status' => UserAccountStatusEnum::class, // Cast pour l'Enum
        ];

        protected $appends = [
            'profile_photo_url',
        ];

        // Méthodes pour Laravel Impersonate
        public function canImpersonate(): bool
        {
            return $this->hasRole('Admin');
        }

        public function canBeImpersonated(): bool
        {
            return !$this->hasRole('Admin');
        }

        // Relations avec les profils spécifiques (un utilisateur a un seul type de profil)
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

        // Relations avec les logs et documents générés
        public function auditLogs(): HasMany
        {
            return $this->hasMany(AuditLog::class, 'user_id');
        }

        public function generatedDocuments(): HasMany
        {
            return $this->hasMany(Document::class, 'generated_by_user_id');
        }
    }