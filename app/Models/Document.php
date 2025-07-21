<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class Document extends Model
    {
        use HasFactory;

        protected $fillable = [
            'document_id', 'document_type_id', 'file_path', 'generation_date',
            'version', 'related_entity_type', 'related_entity_id', 'generated_by_user_id',
        ];

        protected $casts = [
            'generation_date' => 'datetime',
        ];

        // Relations
        public function documentType(): BelongsTo
        {
            return $this->belongsTo(DocumentType::class);
        }

        public function generatedBy(): BelongsTo
        {
            return $this->belongsTo(User::class, 'generated_by_user_id');
        }

        public function relatedEntity(): MorphTo
        {
            return $this->morphTo();
        }
    }