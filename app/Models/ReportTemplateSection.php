<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplateSection extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'report_template_id',
        'title',
        'default_content',
        'order',
        'is_mandatory', // Si la section est obligatoire
    ];

    protected $casts = [
        'order' => 'int',
        'is_mandatory' => 'boolean',
    ];

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }
}