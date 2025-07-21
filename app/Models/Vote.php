<?php

    namespace App\Models;

    use App\Enums\VoteDecisionEnum;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphMany;

    class Vote extends Model
    {
        use HasFactory;

        protected $fillable = [
            'vote_id', 'commission_session_id', 'report_id', 'teacher_id',
            'vote_decision_id', 'comment', 'vote_date', 'vote_round',
        ];

        protected $casts = [
            'vote_date' => 'datetime',
            'vote_decision_id' => VoteDecisionEnum::class, // Cast pour l'Enum
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

        public function teacher(): BelongsTo
        {
            return $this->belongsTo(Teacher::class);
        }

        public function voteDecision(): BelongsTo
        {
            return $this->belongsTo(VoteDecision::class);
        }

        public function auditLogs(): MorphMany
        {
            return $this->morphMany(AuditLog::class, 'auditable');
        }
    }