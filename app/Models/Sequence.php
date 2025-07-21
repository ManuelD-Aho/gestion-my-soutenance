<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class Sequence extends Model
    {
        use HasFactory;

        protected $fillable = ['name', 'year', 'value'];

        // Clé primaire composite
        protected $primaryKey = ['name', 'year'];
        public $incrementing = false;
        protected $keyType = 'string'; // Les clés primaires sont des strings (name, year)
    }