<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class ConformityCriterion extends Model
    {
        use HasFactory;

        protected $fillable = ['label', 'description', 'is_active'];

        protected $casts = [
            'is_active' => 'boolean',
        ];

        // Relations
        public function conformityCheckDetails(): HasMany
        {
            return $this->hasMany(ConformityCheckDetail::class); // Assurez-vous de créer ce modèle
        }
    }