<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Fonction extends Model
    {
        use HasFactory;

        protected $fillable = ['name'];

        // Relations
        public function teacherFunctionHistory(): HasMany
        {
            return $this->hasMany(TeacherFunctionHistory::class);
        }
    }
