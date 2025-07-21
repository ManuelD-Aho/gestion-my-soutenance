<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class ReportTemplateSection extends Model
    {
        use HasFactory;

        protected $fillable = ['report_template_id', 'title', 'default_content', 'order'];

        // Relations
        public function reportTemplate(): BelongsTo
        {
            return $this->belongsTo(ReportTemplate::class);
        }
    }