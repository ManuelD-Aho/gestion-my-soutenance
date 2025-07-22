<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSection extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'report_id',
        'title',
        'content',
        'order',
    ];

    protected $casts = [
        'order' => 'int',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
