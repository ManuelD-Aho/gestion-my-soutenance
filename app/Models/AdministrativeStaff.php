<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class AdministrativeStaff extends Model
    {
        use HasFactory;

        protected $fillable = [
            'staff_id', 'first_name', 'last_name', 'professional_phone', 'professional_email',
            'service_assignment_date', 'key_responsibilities', 'user_id',
            'date_of_birth', 'place_of_birth', 'country_of_birth', 'nationality', 'gender',
            'address', 'city', 'postal_code', 'personal_phone', 'personal_secondary_email',
        ];

        protected $casts = [
            'date_of_birth' => 'date',
            'service_assignment_date' => 'date',
            'gender' => \App\Enums\GenderEnum::class, // Assurez-vous de créer cet Enum si utilisé
        ];

        // Relations
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