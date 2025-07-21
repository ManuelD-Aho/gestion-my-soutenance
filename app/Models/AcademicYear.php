<?php

    namespace App\Models;

    use App\Enums\AcademicYearStatusEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class AcademicYear extends Model
    {
        use HasFactory;

        protected $fillable = ['label', 'start_date', 'end_date', 'is_active'];

        protected $casts = [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'status' => AcademicYearStatusEnum::class, // Si vous ajoutez un champ 'status'
        ];

        // Relations
        public function enrollments(): HasMany
        {
            return $this->hasMany(Enrollment::class);
        }

        public function reports(): HasMany
        {
            return $this->hasMany(Report::class);
        }

        public function penalties(): HasMany
        {
            return $this->hasMany(Penalty::class);
        }
    }