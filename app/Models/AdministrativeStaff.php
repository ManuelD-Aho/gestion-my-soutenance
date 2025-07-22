<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdministrativeStaff extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'staff_id', // ID métier généré
        'first_name',
        'last_name',
        'professional_phone',
        'professional_email',
        'service_assignment_date',
        'key_responsibilities',
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
        'service_assignment_date' => 'date',
        'gender' => GenderEnum::class,
        'is_active' => 'boolean',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class, 'admin_staff_id');
    }

    public function reclamations(): HasMany
    {
        return $this->hasMany(Reclamation::class, 'admin_staff_id');
    }
}
