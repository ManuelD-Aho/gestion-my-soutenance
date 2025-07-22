<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserAccountStatusEnum;
use App\Models\Team;
use App\Models\User;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role; // Import du service

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class); // Instancier le service

        // 1. Créer les Permissions
        $permissions = [
            // Administrateur
            'manage_system_settings', 'manage_users', 'manage_roles_permissions',
            'manage_referentials', 'view_audit_logs', 'view_horizon_dashboard',
            'manage_academic_years', 'manage_all_reports', 'impersonate_users',

            // Étudiant
            'view_student_dashboard', 'manage_own_profile', 'submit_own_report',
            'view_own_documents', 'manage_own_reclamations',

            // Responsable Scolarité
            'view_rs_dashboard', 'manage_students_rs', 'validate_internships',
            'manage_penalties', 'generate_official_documents', 'handle_student_reclamations',
            'manage_enrollments', 'manage_grades',

            // Agent de Conformité
            'view_conformity_dashboard', 'check_report_conformity',

            // Membre Commission
            'view_commission_dashboard', 'view_commission_sessions', 'manage_pvs', 'vote_on_reports',

            // Président Commission
            'manage_commission_sessions_president', 'arbitrate_commission_votes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Créer les Rôles et leur assigner les Permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $studentRole = Role::firstOrCreate(['name' => 'Etudiant']);
        $studentRole->givePermissionTo([
            'view_student_dashboard', 'manage_own_profile', 'submit_own_report',
            'view_own_documents', 'manage_own_reclamations',
        ]);

        $rsRole = Role::firstOrCreate(['name' => 'Responsable Scolarite']);
        $rsRole->givePermissionTo([
            'view_rs_dashboard', 'manage_students_rs', 'validate_internships',
            'manage_penalties', 'generate_official_documents', 'handle_student_reclamations',
            'manage_enrollments', 'manage_grades',
        ]);

        $conformityAgentRole = Role::firstOrCreate(['name' => 'Agent de Conformite']);
        $conformityAgentRole->givePermissionTo([
            'view_conformity_dashboard', 'check_report_conformity',
        ]);

        $commissionMemberRole = Role::firstOrCreate(['name' => 'Membre Commission']);
        $commissionMemberRole->givePermissionTo([
            'view_commission_dashboard', 'view_commission_sessions', 'manage_pvs', 'vote_on_reports',
        ]);

        $commissionPresidentRole = Role::firstOrCreate(['name' => 'President Commission']);
        $commissionPresidentRole->givePermissionTo([
            'view_commission_dashboard', 'view_commission_sessions', 'manage_pvs', 'vote_on_reports',
            'manage_commission_sessions_president', 'arbitrate_commission_votes',
        ]);

        // 3. Créer les utilisateurs par défaut
        $currentYear = (int) date('Y');

        // Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'ahopaul18@gmail.com'],
            [
                'user_id' => $uniqueIdGeneratorService->generate('SYS', $currentYear),
                'name' => 'Admin Système',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]
        );
        $adminUser->assignRole($adminRole);
        if ($adminUser->ownedTeams->isEmpty()) {
            $adminUser->ownedTeams()->save(Team::forceCreate([
                'user_id' => $adminUser->id,
                'name' => $adminUser->name."'s Team",
                'personal_team' => true,
            ]));
        }

        // Étudiant
        $studentUser = User::firstOrCreate(
            ['email' => 'manuelpoan@gmail.com'],
            [
                'user_id' => $uniqueIdGeneratorService->generate('ETU', $currentYear),
                'name' => 'Manuel Poan',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]
        );
        $studentUser->assignRole($studentRole);

        // Responsable Scolarité
        $rsUser = User::firstOrCreate(
            ['email' => 'rs@mysoutenance.com'],
            [
                'user_id' => $uniqueIdGeneratorService->generate('ADM', $currentYear),
                'name' => 'Responsable Scolarité',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]
        );
        $rsUser->assignRole($rsRole);

        // Agent de Conformité
        $conformityAgentUser = User::firstOrCreate(
            ['email' => 'conformite@mysoutenance.com'],
            [
                'user_id' => $uniqueIdGeneratorService->generate('ADM', $currentYear),
                'name' => 'Agent Conformité',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]
        );
        $conformityAgentUser->assignRole($conformityAgentRole);

        // Membre Commission
        $memberUser = User::firstOrCreate(
            ['email' => 'membre@mysoutenance.com'],
            [
                'user_id' => $uniqueIdGeneratorService->generate('ENS', $currentYear),
                'name' => 'Membre Commission',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]
        );
        $memberUser->assignRole($commissionMemberRole);

        // Président Commission
        $presidentUser = User::firstOrCreate(
            ['email' => 'president@mysoutenance.com'],
            [
                'user_id' => $uniqueIdGeneratorService->generate('ENS', $currentYear),
                'name' => 'Président Commission',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserAccountStatusEnum::ACTIVE,
            ]
        );
        $presidentUser->assignRole($commissionPresidentRole);
    }
}
