<?php

namespace Database\Seeders;

use App\Models\AcademicDecision;
use App\Models\AcademicYear;
use App\Models\Action;
use App\Models\ConformityCriterion;
use App\Models\ConformityStatus;
use App\Models\DocumentType;
use App\Models\Fonction;
use App\Models\Grade;
use App\Models\JuryRole;
use App\Models\Notification;
use App\Models\MatriceNotificationRule;
use App\Models\PaymentStatus;
use App\Models\PenaltyStatus;
use App\Models\PvApprovalDecision;
use App\Models\PvStatus;
use App\Models\ReclamationStatus;
use App\Models\ReportStatus;
use App\Models\ReportTemplate;
use App\Models\ReportTemplateSection;
use App\Models\StudyLevel;
use App\Models\SystemParameter;
use App\Models\Ue;
use App\Models\VoteDecision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\UniqueIdGeneratorService; // Import du service

class ReferentialSeeder extends Seeder
{
    public function run(): void
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class); // Instancier le service
        $currentYear = (int)date('Y');

        // Désactiver les événements pour éviter les audits lors du seeding des référentiels
        DB::withoutEvents(function () use ($uniqueIdGeneratorService, $currentYear) {

            // Academic Decisions
            AcademicDecision::firstOrCreate(['name' => 'Admis'], ['description' => 'L\'étudiant a validé son année/cycle.']);
            AcademicDecision::firstOrCreate(['name' => 'Ajourné'], ['description' => 'L\'étudiant doit repasser des épreuves ou refaire son année.']);
            AcademicDecision::firstOrCreate(['name' => 'Exclu'], ['description' => 'L\'étudiant est exclu de la formation.']);

            // Academic Years
            AcademicYear::firstOrCreate(['label' => '2023-2024'], [
                'academic_year_id' => $uniqueIdGeneratorService->generate('AY', 2023),
                'start_date' => '2023-09-01',
                'end_date' => '2024-08-31',
                'is_active' => false,
                'report_submission_deadline' => '2024-07-15 23:59:59',
            ]);
            AcademicYear::firstOrCreate(['label' => '2024-2025'], [
                'academic_year_id' => $uniqueIdGeneratorService->generate('AY', 2024),
                'start_date' => '2024-09-01',
                'end_date' => '2025-08-31',
                'is_active' => true,
                'report_submission_deadline' => '2025-07-15 23:59:59',
            ]);

            // Actions (for AuditLog and Notification Rules)
            $actions = [
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
            foreach ($actions as $action) {
                Action::firstOrCreate(['code' => $action['code']], $action);
            }

            // Conformity Criteria
            $conformityCriteria = [
                ['label' => 'Respect de la page de garde', 'description' => 'Vérifie la conformité de la page de garde (logo, titres, noms).', 'is_active' => true, 'type' => 'MANUAL', 'version' => 1, 'code' => 'PAGE_GARDE_CHECK'],
                ['label' => 'Présence du résumé (Abstract)', 'description' => 'Vérifie que le rapport contient un résumé.', 'is_active' => true, 'type' => 'MANUAL', 'version' => 1, 'code' => 'ABSTRACT_PRESENCE'],
                ['label' => 'Formatage de la bibliographie', 'description' => 'Vérifie que la bibliographie est formatée selon les consignes.', 'is_active' => true, 'type' => 'MANUAL', 'version' => 1, 'code' => 'BIBLIO_FORMAT'],
                ['label' => 'Délais de soumission respectés', 'description' => 'Vérification automatique si le rapport a été soumis avant la date limite.', 'is_active' => true, 'type' => 'AUTOMATIC', 'version' => 1, 'code' => 'DEADLINE_RESPECTED'],
                ['label' => 'Nombre minimal de pages', 'description' => 'Vérification automatique du nombre de pages du rapport.', 'is_active' => true, 'type' => 'AUTOMATIC', 'version' => 1, 'code' => 'MIN_PAGE_COUNT'],
            ];
            foreach ($conformityCriteria as $criterion) {
                ConformityCriterion::firstOrCreate(['code' => $criterion['code']], $criterion);
            }

            // Conformity Statuses
            ConformityStatus::firstOrCreate(['name' => 'Conforme']);
            ConformityStatus::firstOrCreate(['name' => 'Non Conforme']);
            ConformityStatus::firstOrCreate(['name' => 'Non Applicable']);

            // Document Types
            DocumentType::firstOrCreate(['name' => 'Rapport de Soutenance'], ['is_required' => true]);
            DocumentType::firstOrCreate(['name' => 'Procès-Verbal'], ['is_required' => true]);
            DocumentType::firstOrCreate(['name' => 'Bulletin de Notes'], ['is_required' => false]);
            DocumentType::firstOrCreate(['name' => 'Attestation'], ['is_required' => false]);
            DocumentType::firstOrCreate(['name' => 'Reçu de Paiement'], ['is_required' => false]);
            DocumentType::firstOrCreate(['name' => 'Export de Données'], ['is_required' => false]);

            // Fonctions (Personnel)
            Fonction::firstOrCreate(['name' => 'Responsable Scolarité'], ['description' => 'Gère les dossiers administratifs et académiques des étudiants.']);
            Fonction::firstOrCreate(['name' => 'Agent de Conformité'], ['description' => 'Vérifie la conformité des rapports de soutenance.']);
            Fonction::firstOrCreate(['name' => 'Secrétaire de Direction'], ['description' => 'Assiste la direction dans les tâches administratives.']);

            // Grades (Enseignants)
            Grade::firstOrCreate(['name' => 'Professeur', 'abbreviation' => 'PR']);
            Grade::firstOrCreate(['name' => 'Maître de Conférences', 'abbreviation' => 'MCF']);
            Grade::firstOrCreate(['name' => 'Assistant', 'abbreviation' => 'ASS']);

            // Jury Roles
            JuryRole::firstOrCreate(['name' => 'Président du Jury']);
            JuryRole::firstOrCreate(['name' => 'Rapporteur']);
            JuryRole::firstOrCreate(['name' => 'Membre du Jury']);
            JuryRole::firstOrCreate(['name' => 'Directeur de Mémoire']);

            // Payment Statuses
            PaymentStatus::firstOrCreate(['name' => 'En attente de paiement']);
            PaymentStatus::firstOrCreate(['name' => 'Payé']);
            PaymentStatus::firstOrCreate(['name' => 'Paiement partiel']);
            PaymentStatus::firstOrCreate(['name' => 'En retard de paiement']);

            // Penalty Statuses
            PenaltyStatus::firstOrCreate(['name' => 'Due']);
            PenaltyStatus::firstOrCreate(['name' => 'Réglée']);
            PenaltyStatus::firstOrCreate(['name' => 'Annulée']);

            // Pv Approval Decisions
            PvApprovalDecision::firstOrCreate(['name' => 'Approuvé']);
            PvApprovalDecision::firstOrCreate(['name' => 'Modification Demandée']);
            PvApprovalDecision::firstOrCreate(['name' => 'Rejeté']);

            // Pv Statuses
            PvStatus::firstOrCreate(['name' => 'Brouillon']);
            PvStatus::firstOrCreate(['name' => 'En attente d\'approbation']);
            PvStatus::firstOrCreate(['name' => 'Validé']);
            PvStatus::firstOrCreate(['name' => 'Rejeté']);
            PvStatus::firstOrCreate(['name' => 'Archivé']);
            PvStatus::firstOrCreate(['name' => 'En révision']);

            // Reclamation Statuses
            ReclamationStatus::firstOrCreate(['name' => 'Ouverte']);
            ReclamationStatus::firstOrCreate(['name' => 'En cours de traitement']);
            ReclamationStatus::firstOrCreate(['name' => 'Résolue']);
            ReclamationStatus::firstOrCreate(['name' => 'Clôturée']);

            // Report Statuses
            ReportStatus::firstOrCreate(['name' => 'Brouillon', 'workflow_step' => 10]);
            ReportStatus::firstOrCreate(['name' => 'Soumis', 'workflow_step' => 20]);
            ReportStatus::firstOrCreate(['name' => 'Nécessite Correction', 'workflow_step' => 30]);
            ReportStatus::firstOrCreate(['name' => 'En vérification conformité', 'workflow_step' => 40]);
            ReportStatus::firstOrCreate(['name' => 'En Commission', 'workflow_step' => 50]);
            ReportStatus::firstOrCreate(['name' => 'Validé', 'workflow_step' => 60]);
            ReportStatus::firstOrCreate(['name' => 'Refusé', 'workflow_step' => 70]);
            ReportStatus::firstOrCreate(['name' => 'Archivé', 'workflow_step' => 80]);

            // Report Templates
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

            // Study Levels
            StudyLevel::firstOrCreate(['name' => 'Licence 1', 'description' => 'Première année de Licence.']);
            StudyLevel::firstOrCreate(['name' => 'Licence 2', 'description' => 'Deuxième année de Licence.']);
            StudyLevel::firstOrCreate(['name' => 'Licence 3', 'description' => 'Troisième année de Licence.']);
            StudyLevel::firstOrCreate(['name' => 'Master 1', 'description' => 'Première année de Master.']);
            StudyLevel::firstOrCreate(['name' => 'Master 2', 'description' => 'Deuxième année de Master.']);

            // System Parameters
            SystemParameter::firstOrCreate(['key' => 'MAX_LOGIN_ATTEMPTS'], ['value' => '5', 'description' => 'Nombre maximal de tentatives de connexion échouées avant blocage.', 'type' => 'int']);
            SystemParameter::firstOrCreate(['key' => 'ACCOUNT_LOCKOUT_DURATION_MINUTES'], ['value' => '30', 'description' => 'Durée de blocage d\'un compte après échecs de connexion (en minutes).', 'type' => 'int']);
            SystemParameter::firstOrCreate(['key' => 'PV_APPROVAL_DEADLINE_DAYS'], ['value' => '7', 'description' => 'Nombre de jours pour l\'approbation d\'un PV par les membres de la commission.', 'type' => 'int']);
            SystemParameter::firstOrCreate(['key' => 'IMPORT_ASYNC_THRESHOLD'], ['value' => '100', 'description' => 'Nombre de lignes à partir duquel une importation est traitée de manière asynchrone.', 'type' => 'int']);
            SystemParameter::firstOrCreate(['key' => 'LATE_SUBMISSION_PENALTY_AMOUNT'], ['value' => '10000', 'description' => 'Montant de la pénalité pour soumission tardive de rapport.', 'type' => 'float']);
            SystemParameter::firstOrCreate(['key' => 'REPORT_MIN_PAGES'], ['value' => '30', 'description' => 'Nombre minimum de pages pour un rapport de soutenance.', 'type' => 'int']);

            // UEs
            Ue::firstOrCreate(['name' => 'Développement Logiciel', 'credits' => 60]);
            Ue::firstOrCreate(['name' => 'Réseaux et Sécurité', 'credits' => 60]);

            // Vote Decisions
            VoteDecision::firstOrCreate(['name' => 'Approuvé']);
            VoteDecision::firstOrCreate(['name' => 'Refusé']);
            VoteDecision::firstOrCreate(['name' => 'Approuvé sous réserve']);
            VoteDecision::firstOrCreate(['name' => 'Abstention']);

            // Matrice Notification Rules
            $actionReportSubmitted = Action::where('code', 'REPORT_SUBMITTED')->first();
            $actionReportStatusUpdated = Action::where('code', 'REPORT_STATUS_UPDATED')->first();
            $actionAccountActivated = Action::where('code', 'ACCOUNT_ACTIVATED')->first();
            $actionPenaltyApplied = Action::where('code', 'PENALTY_APPLIED')->first();
            $actionPvReadyForApproval = Action::where('code', 'PV_READY_FOR_APPROVAL')->first();

            if ($actionReportSubmitted) {
                MatriceNotificationRule::firstOrCreate(
                    ['action_id' => $actionReportSubmitted->id, 'recipient_role_name' => 'Agent de Conformite', 'channel' => 'Interne'],
                    ['mailable_class_name' => null, 'is_active' => true]
                );
            }
            if ($actionReportStatusUpdated) {
                MatriceNotificationRule::firstOrCreate(
                    ['action_id' => $actionReportStatusUpdated->id, 'recipient_role_name' => 'Etudiant', 'channel' => 'Email'],
                    ['mailable_class_name' => \App\Mail\ReportNeedsCorrectionMail::class, 'is_active' => true]
                );
                MatriceNotificationRule::firstOrCreate(
                    ['action_id' => $actionReportStatusUpdated->id, 'recipient_role_name' => 'Etudiant', 'channel' => 'Interne'],
                    ['mailable_class_name' => null, 'is_active' => true]
                );
            }
            if ($actionAccountActivated) {
                MatriceNotificationRule::firstOrCreate(
                    ['action_id' => $actionAccountActivated->id, 'recipient_role_name' => 'Etudiant', 'channel' => 'Email'],
                    ['mailable_class_name' => \App\Mail\AccountActivatedMail::class, 'is_active' => true]
                );
            }
            if ($actionPenaltyApplied) {
                MatriceNotificationRule::firstOrCreate(
                    ['action_id' => $actionPenaltyApplied->id, 'recipient_role_name' => 'Etudiant', 'channel' => 'Interne'],
                    ['mailable_class_name' => null, 'is_active' => true]
                );
            }
            if ($actionPvReadyForApproval) {
                MatriceNotificationRule::firstOrCreate(
                    ['action_id' => $actionPvReadyForApproval->id, 'recipient_role_name' => 'Membre Commission', 'channel' => 'Interne'],
                    ['mailable_class_name' => null, 'is_active' => true]
                );
            }
        });
    }
}