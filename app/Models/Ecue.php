<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Ecue extends Model
    {
        use HasFactory;

        protected $fillable = ['name', 'ue_id', 'credits'];

        // Relations
        public function ue(): BelongsTo
        {
            return $this->belongsTo(Ue::class);
        }
    }