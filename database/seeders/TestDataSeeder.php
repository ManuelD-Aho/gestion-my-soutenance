<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AdministrativeStaff;
use App\Models\Company;
use App\Models\Enrollment;
use App\Models\Internship;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Fonction;
use App\Models\Grade;
use App\Models\StudyLevel;
use App\Models\PaymentStatus;
use App\Models\AcademicDecision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\GenderEnum;
use App\Enums\UserAccountStatusEnum;
use App\Services\UniqueIdGeneratorService;
use Spatie\Permission\Models\Role;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int)date('Y');

        // Récupérer les rôles
        $studentRole = Role::where('name', 'Etudiant')->first();
        $rsRole = Role::where('name', 'Responsable Scolarite')->first();
        $conformityAgentRole = Role::where('name', 'Agent de Conformite')->first();
        $memberRole = Role::where('name', 'Membre Commission')->first();
        $presidentRole = Role::where('name', 'President Commission')->first();

        // Récupérer les référentiels
        $activeYear = AcademicYear::where('is_active', true)->first();
        $master2Level = StudyLevel::where('name', 'Master 2')->first();
        $licence3Level = StudyLevel::where('name', 'Licence 3')->first();
        $paidStatus = PaymentStatus::where('name', 'Payé')->first();
        $pendingStatus = PaymentStatus::where('name', 'En attente de paiement')->first();
        $admisDecision = AcademicDecision::where('name', 'Admis')->first();
        $profGrade = Grade::where('name', 'Professeur')->first();
        $mcfGrade = Grade::where('name', 'Maître de Conférences')->first();
        $rsFonction = Fonction::where('name', 'Responsable Scolarité')->first();
        $conformityFonction = Fonction::where('name', 'Agent de Conformité')->first();


        // Créer des utilisateurs et profils si non existants
        $manuelUser = User::where('email', 'manuelpoan@gmail.com')->first();
        if (!$manuelUser) {
            $manuelUser = User::create([
                'user_id' => $uniqueIdGeneratorService->generate('USR', $currentYear),
                'name' => 'Manuel Poan',
                'email' => 'manuelpoan@gmail.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]);
            $manuelUser->assignRole($studentRole);
        }

        $rsUser = User::where('email', 'rs@mysoutenance.com')->first();
        if (!$rsUser) {
            $rsUser = User::create([
                'user_id' => $uniqueIdGeneratorService->generate('USR', $currentYear),
                'name' => 'Responsable Scolarité',
                'email' => 'rs@mysoutenance.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]);
            $rsUser->assignRole($rsRole);
        }

        $conformityAgentUser = User::where('email', 'conformite@mysoutenance.com')->first();
        if (!$conformityAgentUser) {
            $conformityAgentUser = User::create([
                'user_id' => $uniqueIdGeneratorService->generate('USR', $currentYear),
                'name' => 'Agent Conformité',
                'email' => 'conformite@mysoutenance.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]);
            $conformityAgentUser->assignRole($conformityAgentRole);
        }

        $memberUser = User::where('email', 'membre@mysoutenance.com')->first();
        if (!$memberUser) {
            $memberUser = User::create([
                'user_id' => $uniqueIdGeneratorService->generate('USR', $currentYear),
                'name' => 'Membre Commission',
                'email' => 'membre@mysoutenance.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]);
            $memberUser->assignRole($memberRole);
        }

        $presidentUser = User::where('email', 'president@mysoutenance.com')->first();
        if (!$presidentUser) {
            $presidentUser = User::create([
                'user_id' => $uniqueIdGeneratorService->generate('USR', $currentYear),
                'name' => 'Président Commission',
                'email' => 'president@mysoutenance.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]);
            $presidentUser->assignRole($presidentRole);
        }

        // Créer les profils métier et les lier aux utilisateurs
        $manuelStudent = Student::firstOrCreate(
            ['user_id' => $manuelUser->id],
            [
                'student_card_number' => $uniqueIdGeneratorService->generate('ETU', $currentYear),
                'first_name' => 'Manuel',
                'last_name' => 'Poan',
                'email_contact_personnel' => 'manuelpoan@gmail.com',
                'date_of_birth' => '2000-01-15',
                'gender' => GenderEnum::MASCULIN,
                'is_active' => true,
            ]
        );

        $rsStaff = AdministrativeStaff::firstOrCreate(
            ['user_id' => $rsUser->id],
            [
                'staff_id' => $uniqueIdGeneratorService->generate('ADM', $currentYear),
                'first_name' => 'Alice',
                'last_name' => 'Durand',
                'professional_email' => 'rs@mysoutenance.com',
                'service_assignment_date' => now(),
                'is_active' => true,
            ]
        );
        if ($rsStaff && $rsFonction) {
            $rsStaff->functionHistory()->firstOrCreate(
                ['function_id' => $rsFonction->id, 'start_date' => now()->subYears(1)],
                ['end_date' => null]
            );
        }


        $conformityStaff = AdministrativeStaff::firstOrCreate(
            ['user_id' => $conformityAgentUser->id],
            [
                'staff_id' => $uniqueIdGeneratorService->generate('ADM', $currentYear),
                'first_name' => 'Bob',
                'last_name' => 'Martin',
                'professional_email' => 'conformite@mysoutenance.com',
                'service_assignment_date' => now(),
                'is_active' => true,
            ]
        );
        if ($conformityStaff && $conformityFonction) {
            $conformityStaff->functionHistory()->firstOrCreate(
                ['function_id' => $conformityFonction->id, 'start_date' => now()->subMonths(6)],
                ['end_date' => null]
            );
        }

        $memberTeacher = Teacher::firstOrCreate(
            ['user_id' => $memberUser->id],
            [
                'teacher_id' => $uniqueIdGeneratorService->generate('ENS', $currentYear),
                'first_name' => 'Dr.',
                'last_name' => 'Dupont',
                'professional_email' => 'membre@mysoutenance.com',
                'is_active' => true,
            ]
        );
        if ($memberTeacher && $mcfGrade) {
            $memberTeacher->gradeHistory()->firstOrCreate(
                ['grade_id' => $mcfGrade->id, 'acquisition_date' => now()->subYears(5)]
            );
        }

        $presidentTeacher = Teacher::firstOrCreate(
            ['user_id' => $presidentUser->id],
            [
                'teacher_id' => $uniqueIdGeneratorService->generate('ENS', $currentYear),
                'first_name' => 'Prof.',
                'last_name' => 'Lefevre',
                'professional_email' => 'president@mysoutenance.com',
                'is_active' => true,
            ]
        );
        if ($presidentTeacher && $profGrade) {
            $presidentTeacher->gradeHistory()->firstOrCreate(
                ['grade_id' => $profGrade->id, 'acquisition_date' => now()->subYears(10)]
            );
        }


        // Données académiques
        if ($activeYear && $master2Level && $paidStatus && $admisDecision) {
            Enrollment::firstOrCreate(
                ['student_id' => $manuelStudent->id, 'academic_year_id' => $activeYear->id],
                [
                    'study_level_id' => $master2Level->id,
                    'enrollment_amount' => 150000.00,
                    'enrollment_date' => now(),
                    'payment_status_id' => $paidStatus->id,
                    'payment_date' => now(),
                    'academic_decision_id' => $admisDecision->id,
                ]
            );
        }

        // Entreprise et Stage
        $company = Company::firstOrCreate(
            ['name' => 'Tech Solutions Inc.'],
            [
                'company_id' => $uniqueIdGeneratorService->generate('COMP', $currentYear),
                'activity_sector' => 'Informatique',
                'contact_name' => 'Mme. Smith',
                'contact_email' => 'contact@techsolutions.com',
            ]
        );

        if ($manuelStudent && $company && $rsUser) {
            Internship::firstOrCreate(
                ['student_id' => $manuelStudent->id, 'company_id' => $company->id],
                [
                    'start_date' => '2025-03-01',
                    'end_date' => '2025-08-31',
                    'subject' => 'Développement d\'une application web sécurisée',
                    'company_tutor_name' => 'M. Jean Dupont',
                    'is_validated' => true,
                    'validation_date' => now(),
                    'validated_by_user_id' => $rsUser->id,
                ]
            );
        }
    }
}
