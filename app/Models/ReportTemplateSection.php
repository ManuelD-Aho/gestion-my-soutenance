<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplateSection extends Model
{
    use HasFactory;

    protected $fillable = ['report_template_id', 'title', 'default_content', 'order', 'is_mandatory'];

    protected $casts = [
        'is_mandatory' => 'boolean', // Assure que 'is_mandatory' est traité comme un booléen
    ];

    /**
     * Obtenir le modèle de rapport auquel cette section appartient.
     */
    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }
}
