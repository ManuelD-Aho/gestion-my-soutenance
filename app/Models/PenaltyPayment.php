<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyPayment extends Model
{
    use HasFactory;

    protected $table = 'penalty_payments';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'penalty_id',
        'amount_paid',
        'payment_date',
        'payment_method', // Ex: 'Espèces', 'Virement', 'Chèque'
        'reference_number', // Numéro de transaction, de chèque, etc.
        'recorded_by_staff_id', // Qui a enregistré le paiement
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'payment_date' => 'datetime',
    ];

    public function penalty(): BelongsTo
    {
        return $this->belongsTo(Penalty::class);
    }

    public function recordedByStaff(): BelongsTo
    {
        return $this->belongsTo(AdministrativeStaff::class, 'recorded_by_staff_id');
    }
}