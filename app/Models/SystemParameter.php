<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class SystemParameter extends Model
    {
        use HasFactory;

        protected $primaryKey = 'key';
        public $incrementing = false;
        protected $keyType = 'string';

        protected $fillable = ['key', 'value', 'description', 'type'];

        protected $casts = [
            'value' => 'string', // Sera casté dynamiquement par le service si besoin
        ];
    }