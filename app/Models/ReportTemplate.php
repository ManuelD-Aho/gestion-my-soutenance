<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'template_id', // ID métier généré
        'name',
        'description',
        'version',
        'status', // Ex: 'Active', 'Draft', 'Archived'
    ];

    protected $casts = [
        'version' => 'int',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(ReportTemplateSection::class);
    }
}
