<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Company extends Model
    {
        use HasFactory;

        protected $fillable = [
            'company_id', 'name', 'activity_sector', 'address',
            'contact_name', 'contact_email', 'contact_phone',
        ];

        // Relations
        public function internships(): HasMany
        {
            return $this->hasMany(Internship::class);
        }
    }