<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class ReportTemplate extends Model
    {
        use HasFactory;

        protected $fillable = ['template_id', 'name', 'description', 'version', 'status'];

        // Relations
        public function sections(): HasMany
        {
            return $this->hasMany(ReportTemplateSection::class);
        }
    }