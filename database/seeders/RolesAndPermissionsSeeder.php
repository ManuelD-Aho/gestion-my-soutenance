<?php

namespace Database\Seeders;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Créer les Permissions
        // Permissions Administrateur
        Permission::create(['name' => 'manage_system_settings']);
        Permission::create(['name' => 'manage_users']);
        Permission::create(['name' => 'manage_roles_permissions']);
        Permission::create(['name' => 'manage_referentials']);
        Permission::create(['name' => 'view_audit_logs']);
        Permission::create(['name' => 'view_horizon_dashboard']);
        Permission::create(['name' => 'manage_academic_years']);
        Permission::create(['name' => 'manage_all_reports']); // Admin peut tout voir et forcer
        // Permissions Étudiant
        Permission::create(['name' => 'view_student_dashboard']);
        Permission::create(['name' => 'manage_own_profile']);
        Permission::create(['name' => 'submit_own_report']);
        Permission::create(['name' => 'view_own_documents']);
        Permission::create(['name' => 'manage_own_reclamations']);
        // Permissions Responsable Scolarité
        Permission::create(['name' => 'view_rs_dashboard']);
        Permission::create(['name' => 'manage_students_rs']); // Gérer les fiches étudiants, activer comptes
        Permission::create(['name' => 'validate_internships']);
        Permission::create(['name' => 'manage_penalties']);
        Permission::create(['name' => 'generate_official_documents']);
        Permission::create(['name' => 'handle_student_reclamations']);
        // Permissions Agent de Conformité
        Permission::create(['name' => 'view_conformity_dashboard']);
        Permission::create(['name' => 'check_report_conformity']);
        // Permissions Membre Commission
        Permission::create(['name' => 'view_commission_dashboard']);
        Permission::create(['name' => 'view_commission_sessions']);
        Permission::create(['name' => 'manage_pvs']); // Rédiger/Approuver PV
        Permission::create(['name' => 'vote_on_reports']);
        // Permissions Président Commission (hérite de Membre Commission + spécifiques)
        Permission::create(['name' => 'manage_commission_sessions_president']); // Créer/Clôturer sessions
        Permission::create(['name' => 'arbitrate_commission_votes']); // Forcer décisions

        // 2. Créer les Rôles et leur assigner les Permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all()); // L'admin a toutes les permissions

        $studentRole = Role::create(['name' => 'Etudiant']);
        $studentRole->givePermissionTo([
            'view_student_dashboard', 'manage_own_profile', 'submit_own_report',
            'view_own_documents', 'manage_own_reclamations'
        ]);

        $rsRole = Role::create(['name' => 'Responsable Scolarite']);
        $rsRole->givePermissionTo([
            'view_rs_dashboard', 'manage_students_rs', 'validate_internships',
            'manage_penalties', 'generate_official_documents', 'handle_student_reclamations'
        ]);

        $conformityAgentRole = Role::create(['name' => 'Agent de Conformite']);
        $conformityAgentRole->givePermissionTo([
            'view_conformity_dashboard', 'check_report_conformity'
        ]);

        $commissionMemberRole = Role::create(['name' => 'Membre Commission']);
        $commissionMemberRole->givePermissionTo([
            'view_commission_dashboard', 'view_commission_sessions', 'manage_pvs', 'vote_on_reports'
        ]);

        $commissionPresidentRole = Role::create(['name' => 'President Commission']);
        $commissionPresidentRole->givePermissionTo([
            'view_commission_dashboard', 'view_commission_sessions', 'manage_pvs', 'vote_on_reports',
            'manage_commission_sessions_president', 'arbitrate_commission_votes'
        ]);

        // 3. Créer un utilisateur Admin par défaut
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mysoutenance.com'],
            [
                'user_id' => 'SYS-2025-0001',
                'name' => 'Administrateur Système',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $adminUser->assignRole($adminRole);

// Ajout de la logique pour l'équipe personnelle
        $adminUser->ownedTeams()->save(Team::forceCreate([
            'user_id' => $adminUser->id,
            'name' => explode(' ', $adminUser->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }
}
