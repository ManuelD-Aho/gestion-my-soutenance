<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserAccountStatusEnum;
use App\Exceptions\UserActivationException;
use App\Mail\AccountActivatedMail;
use App\Models\AdministrativeStaff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class UserManagementService
{
    protected UniqueIdGeneratorService $uniqueIdGeneratorService;

    protected NotificationService $notificationService;

    protected AuditService $auditService;

    protected SessionManagementService $sessionManagementService;

    public function __construct(
        UniqueIdGeneratorService $uniqueIdGeneratorService,
        NotificationService $notificationService,
        AuditService $auditService,
        SessionManagementService $sessionManagementService
    ) {
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
        $this->notificationService = $notificationService;
        $this->auditService = $auditService;
        $this->sessionManagementService = $sessionManagementService;
    }

    public function activateStudentAccount(Student $student): User
    {
        try {
            return DB::transaction(function () use ($student) {
                $student = Student::lockForUpdate()->find($student->id);

                if ($student->user) {
                    return $student->user;
                }

                if (empty($student->email_contact_personnel)) {
                    throw new UserActivationException("Impossible d'activer le compte : l'email de contact personnel de l'étudiant est manquant.");
                }

                if (User::where('email', $student->email_contact_personnel)->exists()) {
                    throw new UserActivationException(
                        "Impossible d'activer le compte : l'adresse email '{$student->email_contact_personnel}' est déjà utilisée par un autre utilisateur."
                    );
                }

                $tempPassword = Str::random(12);

                $user = User::create([
                    'user_id' => $this->uniqueIdGeneratorService->generate('ETU', (int) date('Y')),
                    'name' => $student->first_name.' '.$student->last_name,
                    'email' => $student->email_contact_personnel,
                    'password' => Hash::make($tempPassword),
                    'status' => UserAccountStatusEnum::ACTIVE,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('Etudiant');

                $student->user_id = $user->id;
                $student->save();

                $this->notificationService->sendEmail(
                    AccountActivatedMail::class,
                    $user,
                    ['password' => $tempPassword, 'user' => $user]
                );

                $this->auditService->logAction('ACCOUNT_ACTIVATED', $user, ['profile_id' => $student->id, 'profile_type' => 'Student']);

                return $user;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function updateUserStatus(User $user, UserAccountStatusEnum $newStatus, ?string $reason = null): void
    {
        try {
            DB::transaction(function () use ($user, $newStatus, $reason) {
                $oldStatus = $user->status->value;
                $user->status = $newStatus;
                $user->save();

                $this->sessionManagementService->invalidateAllUserSessions($user);

                $this->auditService->logAction('ACCOUNT_STATUS_CHANGED', $user, [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus->value,
                    'reason' => $reason,
                ]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function transitionUserProfile(User $user, string $newProfileType, array $newProfileData): Model
    {
        try {
            return DB::transaction(function () use ($user, $newProfileType, $newProfileData) {
                $oldProfile = $user->student ?? $user->teacher ?? $user->administrativeStaff;
                if ($oldProfile) {
                    $oldProfile->update(['is_active' => false, 'end_date' => now()]);
                    $this->auditService->logAction('USER_OLD_PROFILE_ARCHIVED', $oldProfile, ['user_id' => $user->id, 'profile_type' => get_class($oldProfile)]);
                }

                $modelClass = $this->getModelClassForProfileType($newProfileType);
                $roleName = $this->getRoleNameForProfileType($newProfileType);

                $newProfileData['user_id'] = $user->id;
                $newProfile = $modelClass::create($newProfileData);

                $user->syncRoles([$roleName]);

                $this->sessionManagementService->invalidateAllUserSessions($user);

                $this->auditService->logAction('USER_PROFILE_TRANSITIONED', $user, [
                    'from_profile_type' => $oldProfile ? get_class($oldProfile) : 'None',
                    'to_profile_type' => get_class($newProfile),
                    'new_role' => $roleName,
                ]);

                return $newProfile;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function resetPassword(User $user, string $newPassword): void
    {
        try {
            DB::transaction(function () use ($user, $newPassword) {
                $hashedPassword = Hash::make($newPassword);

                $user->password = $hashedPassword;
                $user->save();

                $this->sessionManagementService->invalidateAllUserSessions($user);

                $this->auditService->logAction('PASSWORD_RESET', $user, ['user_email' => $user->email]);

                $this->notificationService->sendEmail('PASSWORD_RESET_CONFIRMATION', $user, ['user_email' => $user->email]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function assignRoleToUser(User $user, string $roleName): void
    {
        try {
            DB::transaction(function () use ($user, $roleName) {
                $user->syncRoles([$roleName]);

                $this->sessionManagementService->invalidateAllUserSessions($user);

                $this->auditService->logAction('USER_ROLE_ASSIGNED', $user, ['new_role' => $roleName, 'user_email' => $user->email]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function getModelClassForProfileType(string $profileType): string
    {
        return match ($profileType) {
            'student' => Student::class,
            'teacher' => Teacher::class,
            'administrative_staff' => AdministrativeStaff::class,
            default => throw new \InvalidArgumentException("Type de profil inconnu: {$profileType}"),
        };
    }

    private function getRoleNameForProfileType(string $profileType): string
    {
        return match ($profileType) {
            'student' => 'Etudiant',
            'teacher' => 'Enseignant',
            'administrative_staff' => 'Personnel Administratif',
            default => throw new \InvalidArgumentException("Type de profil inconnu: {$profileType}"),
        };
    }
}
