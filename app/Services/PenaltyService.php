<?php

namespace App\Services;

use App\Enums\PenaltyStatusEnum;
use App\Models\AcademicYear;
use App\Models\Penalty;
use App\Models\PenaltyPayment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class PenaltyService
{
    protected UniqueIdGeneratorService $uniqueIdGeneratorService;
    protected AuditService $auditService;
    protected NotificationService $notificationService;

    public function __construct(
        UniqueIdGeneratorService $uniqueIdGeneratorService,
        AuditService $auditService,
        NotificationService $notificationService
    ) {
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function applyPenalty(Student $student, string $type, string $reason, ?float $amount = null, ?User $adminStaffUser = null): Penalty
    {
        if ($type === 'Financière' && ($amount === null || $amount <= 0)) {
            throw new \InvalidArgumentException("Un montant valide est requis pour une pénalité financière.");
        }
        if ($type !== 'Financière' && $type !== 'Administrative') {
            throw new \InvalidArgumentException("Type de pénalité invalide: {$type}.");
        }

        try {
            return DB::transaction(function () use ($student, $type, $reason, $amount, $adminStaffUser) {
                $activeAcademicYear = AcademicYear::where('is_active', true)->firstOrFail(); // Assumer qu'une année active existe

                $penalty = Penalty::create([
                    'penalty_id' => $this->uniqueIdGeneratorService->generate("PEN", (int)date('Y')),
                    'student_id' => $student->id,
                    'academic_year_id' => $activeAcademicYear->id,
                    'type' => $type,
                    'amount' => $amount,
                    'reason' => $reason,
                    'status' => PenaltyStatusEnum::DUE,
                    'creation_date' => now(),
                    'admin_staff_id' => $adminStaffUser?->administrativeStaff?->id,
                ]);

                $this->auditService->logAction("PENALTY_APPLIED", $penalty, ['student_id' => $student->id, 'type' => $type, 'amount' => $amount, 'reason' => $reason]);
                $this->notificationService->processNotificationRules("PENALTY_APPLIED", $penalty, ['student_name' => $student->first_name . " " . $student->last_name, 'penalty_type' => $type, 'amount' => $amount]);

                return $penalty;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function recordPayment(Penalty $penalty, float $amount, string $paymentMethod, ?string $referenceNumber = null, ?User $adminStaffUser = null): Penalty
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Le montant du paiement doit être positif.");
        }
        if ($penalty->status === PenaltyStatusEnum::PAID || $penalty->status === PenaltyStatusEnum::WAIVED) {
            throw new \InvalidArgumentException("Impossible d'enregistrer un paiement pour une pénalité déjà réglée ou annulée.");
        }

        try {
            return DB::transaction(function () use ($penalty, $amount, $paymentMethod, $referenceNumber, $adminStaffUser) {
                PenaltyPayment::create([
                    'penalty_id' => $penalty->id,
                    'amount_paid' => $amount,
                    'payment_date' => now(),
                    'payment_method' => $paymentMethod,
                    'reference_number' => $referenceNumber,
                    'recorded_by_staff_id' => $adminStaffUser?->administrativeStaff?->id,
                ]);

                $totalPaid = $penalty->payments()->sum('amount_paid');

                if ($totalPaid >= $penalty->amount) {
                    $penalty->status = PenaltyStatusEnum::PAID;
                    $penalty->resolution_date = now();
                } else {
                    $penalty->status = PenaltyStatusEnum::DUE;
                }

                $penalty->save();

                $this->auditService->logAction("PENALTY_PAYMENT_RECORDED", $penalty, ['penalty_id' => $penalty->penalty_id, 'amount_paid' => $amount, 'total_paid' => $totalPaid]);

                if ($penalty->status === PenaltyStatusEnum::PAID) {
                    $this->notificationService->processNotificationRules("PENALTY_PAID", $penalty, ['student_name' => $penalty->student->first_name . " " . $penalty->student->last_name, 'penalty_id' => $penalty->penalty_id]);
                }

                return $penalty->refresh();
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function waivePenalty(Penalty $penalty, User $adminStaffUser, string $reason): void
    {
        if ($penalty->status === PenaltyStatusEnum::PAID || $penalty->status === PenaltyStatusEnum::WAIVED) {
            throw new \InvalidArgumentException("Impossible d'annuler une pénalité déjà réglée ou annulée.");
        }
        if (empty($reason)) {
            throw new \InvalidArgumentException("Une raison est obligatoire pour annuler une pénalité.");
        }

        try {
            DB::transaction(function () use ($penalty, $adminStaffUser, $reason) {
                $penalty->status = PenaltyStatusEnum::WAIVED;
                $penalty->resolution_date = now();
                $penalty->save();

                $this->auditService->logAction("PENALTY_WAIVED", $penalty, ['admin_id' => $adminStaffUser->id, 'reason' => $reason]);
                $this->notificationService->processNotificationRules("PENALTY_WAIVED", $penalty, ['student_name' => $penalty->student->first_name . " " . $penalty->student->last_name, 'penalty_id' => $penalty->penalty_id]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function checkStudentEligibility(Student $student): bool
    {
        return !$student->penalties()->where('status', PenaltyStatusEnum::DUE)->exists();
    }
}
