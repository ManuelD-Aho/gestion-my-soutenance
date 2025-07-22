<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Action;
use App\Models\MatriceNotificationRule;
use App\Models\Notification; // Assumer l'existence de ce modèle pour la matrice
use App\Models\User;
use App\Models\UserNotification; // Assumer l'existence de ce modèle pour les notifications internes
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role; // Utilisation du modèle Role de Spatie
use Throwable;

class NotificationService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function sendInternalNotification(string $notificationCode, User $recipient, array $data = []): void
    {
        try {
            $notificationTemplate = Notification::where('code', $notificationCode)->first();
            if (! $notificationTemplate) {
                Log::warning("NotificationService: Attempted to send internal notification with unknown code: {$notificationCode}");

                return;
            }

            $content = $notificationTemplate->content;
            foreach ($data as $key => $value) {
                $content = str_replace("{{{$key}}}", $value, $content);
            }

            UserNotification::create([
                'user_id' => $recipient->id,
                'notification_id' => $notificationTemplate->id,
                'content' => $content,
                'is_read' => false,
                'sent_at' => now(),
            ]);

            // Optionnel: Déclencher un événement pour une notification en temps réel (WebSocket/Livewire)
            // event(new \App\Events\UserNotificationReceived($recipient->id, $content));

            $this->auditService->logAction('NOTIFICATION_SENT_INTERNAL', $recipient, ['notification_code' => $notificationCode, 'recipient_email' => $recipient->email]);
        } catch (Throwable $e) {
            Log::error("NotificationService: Failed to send internal notification for {$recipient->email}: {$e->getMessage()}");
        }
    }

    public function sendEmail(string $mailableClass, User $recipient, array $data = []): void
    {
        try {
            $mailableInstance = new $mailableClass($data);
            Mail::to($recipient->email)->queue($mailableInstance); // Utilisation de la queue pour la résilience

            $this->auditService->logAction('EMAIL_SENT', $recipient, ['mailable_class' => $mailableClass, 'recipient_email' => $recipient->email]);
        } catch (Throwable $e) {
            Log::error("NotificationService: Failed to send email ({$mailableClass}) to {$recipient->email}: {$e->getMessage()}");
        }
    }

    public function processNotificationRules(string $actionCode, ?Model $relatedEntity = null, array $context = []): void
    {
        try {
            $actionModel = Action::where('code', $actionCode)->first();
            if (! $actionModel) {
                Log::warning("NotificationService: Attempted to process notification rules for unknown actionCode: {$actionCode}");

                return;
            }

            $rules = MatriceNotificationRule::where('action_id', $actionModel->id)
                ->where('is_active', true)
                ->get();

            foreach ($rules as $rule) {
                $recipientRole = Role::where('name', $rule->recipient_role_name)->first();
                if (! $recipientRole) {
                    Log::warning("NotificationService: Notification rule {$rule->id} points to an unknown recipient role: {$rule->recipient_role_name}");

                    continue;
                }

                $recipients = User::role($recipientRole->name)->get();

                $notificationData = $context;
                if ($relatedEntity) {
                    $notificationData = array_merge($notificationData, $relatedEntity->toArray());
                }

                $mailableClass = $rule->mailable_class_name; // Assumer que le nom de la classe Mailable est stocké dans la règle

                foreach ($recipients as $recipient) {
                    // Vérifier les préférences de notification de l'utilisateur (logique à implémenter si nécessaire)
                    // Ex: if ($recipient->notificationPreferences->getChannelPreference($actionCode, 'internal')) { ... }

                    if ($rule->channel === 'Interne' || $rule->channel === 'Tous') {
                        $this->sendInternalNotification($actionCode, $recipient, $notificationData);
                    }

                    if (($rule->channel === 'Email' || $rule->channel === 'Tous') && $mailableClass) {
                        $this->sendEmail($mailableClass, $recipient, $notificationData);
                    } elseif (($rule->channel === 'Email' || $rule->channel === 'Tous') && ! $mailableClass) {
                        Log::warning("NotificationService: No Mailable class defined for actionCode {$actionCode} for email sending.");
                    }
                }
            }

            $this->auditService->logAction('NOTIFICATION_RULE_PROCESSED', $relatedEntity, ['action_code' => $actionCode, 'rules_count' => $rules->count()]);
        } catch (Throwable $e) {
            Log::error("NotificationService: Failed to process notification rules for action {$actionCode}: {$e->getMessage()}");
        }
    }
}
