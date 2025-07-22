<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriceNotificationRule extends Model
{
    use HasFactory;

    protected $table = 'matrice_notification_rules';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'action_id', // L'action qui déclenche la règle
        'recipient_role_name', // Nom du rôle Spatie du destinataire
        'channel', // 'Interne', 'Email', 'Tous'
        'mailable_class_name', // Nom complet de la classe Mailable (si canal email)
        'is_active', // Règle active ou non
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    // Pas de relation directe avec Role, car recipient_role_name est une chaîne de caractères.
    // La liaison se fait via le service de notification en utilisant Spatie.
}
