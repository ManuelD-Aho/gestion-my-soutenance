<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class AuditLog extends Model
    {
        use HasFactory;

        protected $fillable = [
            'log_id', 'user_id', 'action_id', 'action_date', 'ip_address', 'user_agent',
            'auditable_type', 'auditable_id', 'details',
        ];

        protected $casts = [
            'action_date' => 'datetime',
            'details' => 'array', // Cast le champ JSON en tableau PHP
        ];

        // Relations
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function action(): BelongsTo
        {
            return $this->belongsTo(Action::class);
        }

        public function auditable(): MorphTo
        {
            return $this->morphTo();
        }
    }