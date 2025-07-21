<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications'; // Table pour les templates de notifications internes
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'code', // Code unique de la notification (ex: 'COMPTE_ACTIVE', 'RAPPORT_VALIDE')
        'subject', // Sujet par défaut pour l'affichage
        'content', // Contenu par défaut avec placeholders (ex: "Votre rapport {{report_title}} a été validé.")
        'is_active',
        'level', // 'INFO', 'WARNING', 'CRITICAL' (pour les préférences utilisateur)
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function notificationRules(): HasMany
    {
        return $this->hasMany(MatriceNotificationRule::class, 'notification_id');
    }
}