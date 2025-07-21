<?php

    namespace App\Models;

    use App\Enums\PvStatusEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Pv extends Model
    {
        use HasFactory;

        protected $fillable = [
            'pv_id', 'commission_session_id', 'report_id', 'type',
            'content', 'author_user_id', 'status', 'approval_deadline',
        ];

        protected $casts = [
            'approval_deadline' => 'datetime',
            'status' => PvStatusEnum::class,
        ];

        // Relations
        public function commissionSession(): BelongsTo
        {
            return $this->belongsTo(CommissionSession::class);
        }

        public function report(): BelongsTo
        {
            return $this->belongsTo(Report::class);
        }

        public function author(): BelongsTo
        {
            return $this->belongsTo(User::class, 'author_user_id');
        }

        public function pvStatus(): BelongsTo
        {
            return $this->belongsTo(PvStatus::class, 'status'); // Si le nom de la colonne est 'status'
        }

        public function approvals(): HasMany
        {
            return $this->hasMany(PvApproval::class); // Assurez-vous de créer ce modèle
        }
    }