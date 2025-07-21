<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Ue extends Model
    {
        use HasFactory;

        protected $fillable = ['name', 'credits'];

        // Relations
        public function ecues(): HasMany
        {
            return $this->hasMany(Ecue::class);
        }
    }