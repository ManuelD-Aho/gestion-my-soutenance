<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\MorphMany;

    class Student extends Model
    {
        use HasFactory;

        protected $fillable = [
            'student_card_number', 'first_name', 'last_name', 'email_contact_personnel', 'user_id',
            'date_of_birth', 'place_of_birth', 'country_of_birth', 'nationality', 'gender',
            'address', 'city', 'postal_code', 'phone', 'secondary_email',
            'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        ];

        protected $casts = [
            'date_of_birth' => 'date',
            'gender' => \App\Enums\GenderEnum::class, // Assurez-vous de créer cet Enum si utilisé
        ];

        // Relations
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function reports(): HasMany
        {
            return $this->hasMany(Report::class);
        }

        public function enrollments(): HasMany
        {
            return $this->hasMany(Enrollment::class);
        }

        public function internships(): HasMany
        {
            return $this->hasMany(Internship::class);
        }

        public function penalties(): HasMany
        {
            return $this->hasMany(Penalty::class);
        }

        public function reclamations(): HasMany
        {
            return $this->hasMany(Reclamation::class);
        }

        public function auditLogs(): MorphMany
        {
            return $this->morphMany(AuditLog::class, 'auditable');
        }
    }