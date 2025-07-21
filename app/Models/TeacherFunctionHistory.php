<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class TeacherFunctionHistory extends Model
    {
        use HasFactory;

        protected $table = 'teacher_function_history'; // Nom de la table pivot

        protected $fillable = ['teacher_id', 'function_id', 'start_date', 'end_date'];

        protected $casts = [
            'start_date' => 'date',
            'end_date' => 'date',
        ];

        // Relations
        public function teacher(): BelongsTo
        {
            return $this->belongsTo(Teacher::class);
        }

        public function function(): BelongsTo
        {
            return $this->belongsTo(Fonction::class);
        }
    }
