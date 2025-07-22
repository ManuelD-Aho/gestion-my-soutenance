<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AcademicYearStatusEnum;
use App\Enums\ConformityStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PenaltyStatusEnum;
use App\Enums\PvApprovalDecisionEnum;
use App\Enums\PvStatusEnum;
use App\Enums\ReclamationStatusEnum;
use App\Enums\ReportStatusEnum;
use App\Enums\VoteDecisionEnum;
use App\Models\AcademicDecision;
use App\Models\AcademicYear;
use App\Models\Action;
use App\Models\AdministrativeStaff;
use App\Models\CommissionSession;
use App\Models\Company;
use App\Models\ConformityCheckDetail;
use App\Models\ConformityCriterion;
use App\Models\ConformityStatus;
use App\Models\DocumentType;
use App\Models\Enrollment;
use App\Models\Fonction;
use App\Models\Grade;
use App\Models\Internship;
use App\Models\JuryRole;
use App\Models\MatriceNotificationRule;
use App\Models\Notification;
use App\Models\PaymentStatus;
use App\Models\Penalty;
use App\Models\PenaltyPayment;
use App\Models\PenaltyStatus;
use App\Models\Pv;
use App\Models\PvApproval;
use App\Models\PvApprovalDecision;
use App\Models\PvStatus;
use App\Models\Reclamation;
use App\Models\ReclamationStatus;
use App\Models\Report;
use App\Models\ReportSection;
use App\Models\ReportStatus;
use App\Models\ReportTemplate;
use App\Models\ReportTemplateSection;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudyLevel;
use App\Models\SystemParameter;
use App\Models\Teacher;
use App\Models\Ue;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDecision;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class ReferentialSeeder extends Seeder
{
    public function run(): void
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');

        Event::fake();

        // Référentiels de base (créés via firstOrCreate pour garantir l'existence des valeurs spécifiques)
        AcademicDecision::firstOrCreate(['name' => 'Admis'], ['description' => 'L\'étudiant a validé son année/cycle.']);
        AcademicDecision::firstOrCreate(['name' => 'Ajourné'], ['description' => 'L\'étudiant doit repasser des épreuves ou refaire son année.']);
        AcademicDecision::firstOrCreate(['name' => 'Exclu'], ['description' => 'L\'étudiant est exclu de la formation.']);

        $academicYear2023 = AcademicYear::firstOrCreate(['label' => '2023-2024'], [
            'academic_year_id' => $uniqueIdGeneratorService->generate('AY', 2023),
            'start_date' => '2023-09-01',
            'end_date' => '2024-08-31',
            'is_active' => false,
            'status' => AcademicYearStatusEnum::ARCHIVED,
            'report_submission_deadline' => '2024-07-15 23:59:59',
        ]);
        $academicYear2024 = AcademicYear::firstOrCreate(['label' => '2024-2025'], [
            'academic_year_id' => $uniqueIdGeneratorService->generate('AY', 2024),
            'start_date' => '2024-09-01',
            'end_date' => '2025-08-31',
            'is_active' => true,
            'status' => AcademicYearStatusEnum::ACTIVE,
            'report_submission_deadline' => '2025-07-15 23:59:59',
        ]);

        $actionsData = [
            ['code' => 'LOGIN_SUCCESS', 'label' => 'Connexion réussie', 'category' => 'Sécurité'],
            ['code' => 'ACCOUNT_ACTIVATED', 'label' => 'Compte utilisateur activé', 'category' => 'Gestion Utilisateur'],
            ['code' => 'ACCOUNT_STATUS_CHANGED', 'label' => 'Statut de compte modifié', 'category' => 'Gestion Utilisateur'],
            ['code' => 'USER_OLD_PROFILE_ARCHIVED', 'label' => 'Ancien profil utilisateur archivé', 'category' => 'Gestion Utilisateur'],
            ['code' => 'USER_PROFILE_TRANSITIONED', 'label' => 'Transition de profil utilisateur', 'category' => 'Gestion Utilisateur'],
            ['code' => 'PASSWORD_RESET', 'label' => 'Mot de passe réinitialisé', 'category' => 'Sécurité'],
            ['code' => 'USER_ROLE_ASSIGNED', 'label' => 'Rôle utilisateur assigné', 'category' => 'Gestion Utilisateur'],
            ['code' => 'REPORT_SUBMITTED', 'label' => 'Rapport de soutenance soumis', 'category' => 'Workflow Rapport'],
            ['code' => 'REPORT_STATUS_UPDATED', 'label' => 'Statut de rapport mis à jour', 'category' => 'Workflow Rapport'],
            ['code' => 'REPORT_CONFORMITY_CHECKED', 'label' => 'Vérification conformité rapport effectuée', 'category' => 'Workflow Rapport'],
            ['code' => 'COMMISSION_SESSION_CREATED', 'label' => 'Session de commission créée', 'category' => 'Workflow Commission'],
            ['code' => 'REPORT_ADDED_TO_SESSION', 'label' => 'Rapport ajouté à une session', 'category' => 'Workflow Commission'],
            ['code' => 'COMMISSION_VOTE_RECORDED', 'label' => 'Vote de commission enregistré', 'category' => 'Workflow Commission'],
            ['code' => 'COMMISSION_SESSION_CLOSED', 'label' => 'Session de commission clôturée', 'category' => 'Workflow Commission'],
            ['code' => 'PV_GENERATED', 'label' => 'Procès-Verbal généré', 'category' => 'Workflow Commission'],
            ['code' => 'PV_APPROVAL_RECORDED', 'label' => 'Approbation de PV enregistrée', 'category' => 'Workflow Commission'],
            ['code' => 'PV_APPROVAL_FORCED', 'label' => 'Approbation de PV forcée', 'category' => 'Workflow Commission'],
            ['code' => 'DOCUMENT_GENERATED', 'label' => 'Document officiel généré', 'category' => 'Gestion Document'],
            ['code' => 'PENALTY_APPLIED', 'label' => 'Pénalité appliquée', 'category' => 'Gestion Administrative'],
            ['code' => 'PENALTY_PAYMENT_RECORDED', 'label' => 'Paiement de pénalité enregistré', 'category' => 'Gestion Administrative'],
            ['code' => 'PENALTY_WAIVED', 'label' => 'Pénalité annulée', 'category' => 'Gestion Administrative'],
            ['code' => 'IMPORT_INITIATED_ASYNC', 'label' => 'Importation de données lancée (asynchrone)', 'category' => 'Import/Export'],
            ['code' => 'IMPORT_COMPLETED_SYNC', 'label' => 'Importation de données terminée (synchrone)', 'category' => 'Import/Export'],
            ['code' => 'NOTIFICATION_SENT_INTERNAL', 'label' => 'Notification interne envoyée', 'category' => 'Communication'],
            ['code' => 'EMAIL_SENT', 'label' => 'Email envoyé', 'category' => 'Communication'],
            ['code' => 'NOTIFICATION_RULE_PROCESSED', 'label' => 'Règle de notification traitée', 'category' => 'Communication'],
        ];
        foreach ($actionsData as $action) {
            Action::firstOrCreate(['code' => $action['code']], $action);
        }

        // Utilisation des factories pour les référentiels si non déjà créés par firstOrCreate
        $conformityCriteria = ConformityCriterion::factory(5)->create(); // Crée 5 critères génériques
        ConformityCriterion::firstOrCreate(['code' => 'PAGE_GARDE_CHECK'], ['label' => 'Respect de la page de garde', 'description' => 'Vérifie la conformité de la page de garde (logo, titres, noms).', 'is_active' => true, 'type' => 'MANUAL', 'version' => 1]);
        ConformityCriterion::firstOrCreate(['code' => 'ABSTRACT_PRESENCE'], ['label' => 'Présence du résumé (Abstract)', 'description' => 'Vérifie que le rapport contient un résumé.', 'is_active' => true, 'type' => 'MANUAL', 'version' => 1]);
        ConformityCriterion::firstOrCreate(['code' => 'BIBLIO_FORMAT'], ['label' => 'Formatage de la bibliographie', 'description' => 'Vérifie que la bibliographie est formatée selon les consignes.', 'is_active' => true, 'type' => 'MANUAL', 'version' => 1]);
        ConformityCriterion::firstOrCreate(['code' => 'DEADLINE_RESPECTED'], ['label' => 'Délais de soumission respectés', 'description' => 'Vérification automatique si le rapport a été soumis avant la date limite.', 'is_active' => true, 'type' => 'AUTOMATIC', 'version' => 1]);
        ConformityCriterion::firstOrCreate(['code' => 'MIN_PAGE_COUNT'], ['label' => 'Nombre minimal de pages', 'description' => 'Vérification automatique du nombre de pages du rapport.', 'is_active' => true, 'type' => 'AUTOMATIC', 'version' => 1]);


        ConformityStatus::firstOrCreate(['name' => ConformityStatusEnum::CONFORME->value]);
        ConformityStatus::firstOrCreate(['name' => ConformityStatusEnum::NON_CONFORME->value]);
        ConformityStatus::firstOrCreate(['name' => ConformityStatusEnum::NON_APPLICABLE->value]);

        DocumentType::firstOrCreate(['name' => DocumentTypeEnum::RAPPORT->value], ['is_required' => true]);
        DocumentType::firstOrCreate(['name' => DocumentTypeEnum::PV->value], ['is_required' => true]);
        DocumentType::firstOrCreate(['name' => DocumentTypeEnum::BULLETIN->value], ['is_required' => false]);
        DocumentType::firstOrCreate(['name' => DocumentTypeEnum::ATTESTATION->value], ['is_required' => false]);
        DocumentType::firstOrCreate(['name' => DocumentTypeEnum::RECU->value], ['is_required' => false]);
        DocumentType::firstOrCreate(['name' => DocumentTypeEnum::EXPORT->value], ['is_required' => false]);

        Fonction::firstOrCreate(['name' => 'Responsable Scolarité'], ['description' => 'Gère les dossiers administratifs et académiques des étudiants.']);
        Fonction::firstOrCreate(['name' => 'Agent de Conformité'], ['description' => 'Vérifie la conformité des rapports de soutenance.']);
        Fonction::firstOrCreate(['name' => 'Secrétaire de Direction'], ['description' => 'Assiste la direction dans les tâches administratives.']);

        Grade::firstOrCreate(['name' => 'Professeur', 'abbreviation' => 'PR']);
        Grade::firstOrCreate(['name' => 'Maître de Conférences', 'abbreviation' => 'MCF']);
        Grade::firstOrCreate(['name' => 'Assistant', 'abbreviation' => 'ASS']);

        JuryRole::firstOrCreate(['name' => 'Président du Jury']);
        JuryRole::firstOrCreate(['name' => 'Rapporteur']);
        JuryRole::firstOrCreate(['name' => 'Membre du Jury']);
        JuryRole::firstOrCreate(['name' => 'Directeur de Mémoire']);

        PaymentStatus::firstOrCreate(['name' => PaymentStatusEnum::PENDING->value]);
        PaymentStatus::firstOrCreate(['name' => PaymentStatusEnum::PAID->value]);
        PaymentStatus::firstOrCreate(['name' => PaymentStatusEnum::PARTIAL->value]);
        PaymentStatus::firstOrCreate(['name' => PaymentStatusEnum::OVERDUE->value]);

        PenaltyStatus::firstOrCreate(['name' => PenaltyStatusEnum::DUE->value]);
        PenaltyStatus::firstOrCreate(['name' => PenaltyStatusEnum::PAID->value]);
        PenaltyStatus::firstOrCreate(['name' => PenaltyStatusEnum::WAIVED->value]);

        PvApprovalDecision::firstOrCreate(['name' => PvApprovalDecisionEnum::APPROVED->value]);
        PvApprovalDecision::firstOrCreate(['name' => PvApprovalDecisionEnum::CHANGES_REQUESTED->value]);
        PvApprovalDecision::firstOrCreate(['name' => PvApprovalDecisionEnum::REJECTED->value]);

        PvStatus::firstOrCreate(['name' => PvStatusEnum::DRAFT->value]);
        PvStatus::firstOrCreate(['name' => PvStatusEnum::PENDING_APPROVAL->value]);
        PvStatus::firstOrCreate(['name' => PvStatusEnum::APPROVED->value]);
        PvStatus::firstOrCreate(['name' => PvStatusEnum::REJECTED->value]);
        PvStatus::firstOrCreate(['name' => PvStatusEnum::ARCHIVED->value]);
        PvStatus::firstOrCreate(['name' => PvStatusEnum::IN_REVISION->value]);

        ReclamationStatus::firstOrCreate(['name' => ReclamationStatusEnum::OPEN->value]);
        ReclamationStatus::firstOrCreate(['name' => ReclamationStatusEnum::IN_PROGRESS->value]);
        ReclamationStatus::firstOrCreate(['name' => ReclamationStatusEnum::RESOLVED->value]);
        ReclamationStatus::firstOrCreate(['name' => ReclamationStatusEnum::CLOSED->value]);

        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::DRAFT->value, 'workflow_step' => 10]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::SUBMITTED->value, 'workflow_step' => 20]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::NEEDS_CORRECTION->value, 'workflow_step' => 30]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::IN_CONFORMITY_CHECK->value, 'workflow_step' => 40]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::IN_COMMISSION_REVIEW->value, 'workflow_step' => 50]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::VALIDATED->value, 'workflow_step' => 60]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::REJECTED->value, 'workflow_step' => 70]);
        ReportStatus::firstOrCreate(['name' => ReportStatusEnum::ARCHIVED->value, 'workflow_step' => 80]);

        $templateStandard = ReportTemplate::firstOrCreate(['name' => 'Modèle Standard de Rapport'], [
            'template_id' => $uniqueIdGeneratorService->generate('TPL', $currentYear),
            'description' => 'Modèle de rapport de soutenance standard pour toutes les spécialités.',
            'version' => '1.0',
            'status' => 'Active',
        ]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Introduction'], ['default_content' => '<p>Présentation du contexte et des objectifs.</p>', 'order' => 10, 'is_mandatory' => true]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Contexte et Problématique'], ['default_content' => '<p>Description du problème étudié.</p>', 'order' => 20, 'is_mandatory' => true]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Méthodologie'], ['default_content' => '<p>Approche et outils utilisés.</p>', 'order' => 30, 'is_mandatory' => true]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Résultats et Analyse'], ['default_content' => '<p>Présentation et interprétation des résultats.</p>', 'order' => 40, 'is_mandatory' => true]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Conclusion et Perspectives'], ['default_content' => '<p>Synthèse et pistes futures.</p>', 'order' => 50, 'is_mandatory' => true]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Bibliographie'], ['default_content' => '<p>Liste des références.</p>', 'order' => 60, 'is_mandatory' => true]);
        ReportTemplateSection::firstOrCreate(['report_template_id' => $templateStandard->id, 'title' => 'Annexes'], ['default_content' => '<p>Documents complémentaires.</p>', 'order' => 70, 'is_mandatory' => false]);

        StudyLevel::firstOrCreate(['name' => 'Licence 1', 'description' => 'Première année de Licence.']);
        StudyLevel::firstOrCreate(['name' => 'Licence 2', 'description' => 'Deuxième année de Licence.']);
        StudyLevel::firstOrCreate(['name' => 'Licence 3', 'description' => 'Troisième année de Licence.']);
        StudyLevel::firstOrCreate(['name' => 'Master 1', 'description' => 'Première année de Master.']);
        StudyLevel::firstOrCreate(['name' => 'Master 2', 'description' => 'Deuxième année de Master.']);

        SystemParameter::firstOrCreate(['key' => 'MAX_LOGIN_ATTEMPTS'], ['value' => '5', 'description' => 'Nombre maximal de tentatives de connexion échouées avant blocage.', 'type' => 'int']);
        SystemParameter::firstOrCreate(['key' => 'ACCOUNT_LOCKOUT_DURATION_MINUTES'], ['value' => '30', 'description' => 'Durée de blocage d\'un compte après échecs de connexion (en minutes).', 'type' => 'int']);
        SystemParameter::firstOrCreate(['key' => 'PV_APPROVAL_DEADLINE_DAYS'], ['value' => '7', 'description' => 'Nombre de jours pour l\'approbation d\'un PV par les membres de la commission.', 'type' => 'int']);
        SystemParameter::firstOrCreate(['key' => 'IMPORT_ASYNC_THRESHOLD'], ['value' => '100', 'description' => 'Nombre de lignes à partir duquel une importation est traitée de manière asynchrone.', 'type' => 'int']);
        SystemParameter::firstOrCreate(['key' => 'LATE_SUBMISSION_PENALTY_AMOUNT'], ['value' => '10000', 'description' => 'Montant de la pénalité pour soumission tardive de rapport.', 'type' => 'float']);
        SystemParameter::firstOrCreate(['key' => 'REPORT_MIN_PAGES'], ['value' => '30', 'description' => 'Nombre minimum de pages pour un rapport de soutenance.', 'type' => 'int']);

        Ue::firstOrCreate(['name' => 'Développement Logiciel', 'credits' => 60]);
        Ue::firstOrCreate(['name' => 'Réseaux et Sécurité', 'credits' => 60]);

        VoteDecision::firstOrCreate(['name' => VoteDecisionEnum::APPROVED->value]);
        VoteDecision::firstOrCreate(['name' => VoteDecisionEnum::REJECTED->value]);
        VoteDecision::firstOrCreate(['name' => VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value]);
        VoteDecision::firstOrCreate(['name' => VoteDecisionEnum::ABSTAIN->value]);

        // Génération de données factices liées
        $students = Student::factory(10)->create();
        $teachers = Teacher::factory(10)->create();
        $administrativeStaff = AdministrativeStaff::factory(10)->create();
        $companies = Company::factory(10)->create();

        // Lier les utilisateurs par défaut aux entités métier
        $studentUser = User::where('email', 'manuelpoan@gmail.com')->first();
        if ($studentUser) {
            $student = Student::where('email_contact_personnel', $studentUser->email)->first();
            if ($student) {
                $student->user_id = $studentUser->id;
                $student->save();
            }
        }

        $rsUser = User::where('email', 'rs@mysoutenance.com')->first();
        if ($rsUser) {
            $rsStaff = AdministrativeStaff::where('professional_email', $rsUser->email)->first();
            if ($rsStaff) {
                $rsStaff->user_id = $rsUser->id;
                $rsStaff->save();
            }
        }

        $conformityAgentUser = User::where('email', 'conformite@mysoutenance.com')->first();
        if ($conformityAgentUser) {
            $conformityStaff = AdministrativeStaff::where('professional_email', $conformityAgentUser->email)->first();
            if ($conformityStaff) {
                $conformityStaff->user_id = $conformityAgentUser->id;
                $conformityStaff->save();
            }
        }

        $memberUser = User::where('email', 'membre@mysoutenance.com')->first();
        if ($memberUser) {
            $memberTeacher = Teacher::where('professional_email', $memberUser->email)->first();
            if ($memberTeacher) {
                $memberTeacher->user_id = $memberUser->id;
                $memberTeacher->save();
            }
        }

        $presidentUser = User::where('email', 'president@mysoutenance.com')->first();
        if ($presidentUser) {
            $presidentTeacher = Teacher::where('professional_email', $presidentUser->email)->first();
            if ($presidentTeacher) {
                $presidentTeacher->user_id = $presidentUser->id;
                $presidentTeacher->save();
            }
        }

        // Créer des inscriptions
        $studyLevels = StudyLevel::all();
        $paymentStatuses = PaymentStatus::all();
        $academicDecisions = AcademicDecision::all();

        foreach ($students as $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'academic_year_id' => $academicYear2024->id,
                'study_level_id' => $studyLevels->random()->id,
                'payment_status_id' => $paymentStatuses->random()->id,
                'academic_decision_id' => $academicDecisions->random()->id,
            ]);
        }

        // Créer des stages
        foreach ($students as $student) {
            Internship::factory()->create([
                'student_id' => $student->id,
                'company_id' => $companies->random()->id,
                'validated_by_user_id' => $rsUser->id,
            ]);
        }

        // Créer des rapports
        foreach ($students as $student) {
            Report::factory()->create([
                'student_id' => $student->id,
                'academic_year_id' => $academicYear2024->id,
                'report_template_id' => $templateStandard->id,
            ]);
        }

        // Créer des sessions de commission
        $commissionSessions = CommissionSession::factory(5)->create([
            'president_teacher_id' => $teachers->random()->id,
        ]);

        // Attacher des enseignants aux sessions de commission
        foreach ($commissionSessions as $session) {
            $session->teachers()->attach($session->president_teacher_id);
            $otherTeachers = $teachers->where('id', '!=', $session->president_teacher_id)->random(rand(2, 4));
            foreach ($otherTeachers as $teacher) {
                $session->teachers()->attach($teacher->id);
            }
        }

        // Attacher des rapports aux sessions de commission et créer des votes
        $reports = Report::all();
        $voteDecisions = VoteDecision::all();

        foreach ($commissionSessions as $session) {
            $reportsForSession = $reports->random(rand(1, min(5, $reports->count())));
            foreach ($reportsForSession as $report) {
                $session->reports()->attach($report->id);

                foreach ($session->teachers as $teacher) {
                    Vote::factory()->create([
                        'commission_session_id' => $session->id,
                        'report_id' => $report->id,
                        'teacher_id' => $teacher->id,
                        'vote_decision_id' => $voteDecisions->random()->id,
                    ]);
                }
            }
        }

        // Créer des PVs
        foreach ($commissionSessions as $session) {
            Pv::factory()->create([
                'commission_session_id' => $session->id,
                'author_user_id' => $session->president->user->id,
            ]);
        }

        // Créer des pénalités
        foreach ($students as $student) {
            if (rand(0, 1)) {
                Penalty::factory()->create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear2024->id,
                    'admin_staff_id' => $administrativeStaff->random()->id,
                ]);
            }
        }

        // Créer des réclamations
        foreach ($students as $student) {
            if (rand(0, 1)) {
                Reclamation::factory()->create([
                    'student_id' => $student->id,
                    'admin_staff_id' => $administrativeStaff->random()->id,
                ]);
            }
        }

        // Créer des règles de notification (exemple)
        $actionReportSubmitted = Action::where('code', 'REPORT_SUBMITTED')->first();
        $roleConformityAgent = Role::where('name', 'Agent de Conformite')->first();
        if ($actionReportSubmitted && $roleConformityAgent) {
            MatriceNotificationRule::firstOrCreate(
                ['action_id' => $actionReportSubmitted->id, 'recipient_role_name' => $roleConformityAgent->name, 'channel' => 'Interne'],
                ['mailable_class_name' => null, 'is_active' => true]
            );
        }
    }
}
