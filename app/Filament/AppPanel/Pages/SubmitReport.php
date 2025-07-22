<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Pages;

use App\Enums\ReportStatusEnum;
use App\Exceptions\IncompleteSubmissionException;
use App\Exceptions\StateConflictException;
use App\Http\Middleware\EnsureStudentIsEligible;
use App\Models\AcademicYear;
use App\Models\Report;
use App\Models\ReportSection;
use App\Models\ReportTemplate;
use App\Services\ReportFlowService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Assurez-vous que ce middleware est bien défini

class SubmitReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string $view = 'filament.app-panel.pages.submit-report';

    protected static ?string $navigationLabel = 'Soumettre Rapport';

    protected static ?string $slug = 'submit-report';

    public ?array $data = [];

    public ?Report $report = null;

    public ?int $currentReportVersion = null;

    protected ReportFlowService $reportFlowService;

    public function boot(ReportFlowService $reportFlowService): void
    {
        $this->reportFlowService = $reportFlowService;
    }

    public function mount(): void
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Votre profil étudiant n\'est pas lié ou actif.')
                ->danger()
                ->send();
            $this->redirect(route('filament.app.pages.dashboard'));

            return;
        }

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        if (! $activeAcademicYear) {
            Notification::make()
                ->title('Année académique non définie')
                ->body('Aucune année académique active n\'est configurée. Veuillez contacter l\'administration.')
                ->danger()
                ->send();
            $this->redirect(route('filament.app.pages.dashboard'));

            return;
        }

        $this->report = Report::where('student_id', $student->id)
            ->where('academic_year_id', $activeAcademicYear->id)
            ->whereIn('status', [ReportStatusEnum::DRAFT, ReportStatusEnum::NEEDS_CORRECTION])
            ->first();

        if ($this->report) {
            $this->currentReportVersion = $this->report->version;
            $this->form->fill([
                'title' => $this->report->title,
                'theme' => $this->report->theme,
                'abstract' => $this->report->abstract,
                'report_template_id' => $this->report->report_template_id,
                'sections' => $this->report->sections->map(fn ($section) => [
                    'title' => $section->title,
                    'content' => $section->content,
                    'order' => $section->order,
                ])->toArray(),
            ]);
        } else {
            $this->form->fill([
                'sections' => [],
            ]);
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Informations Générales du Rapport')
                ->schema([
                    TextInput::make('title')
                        ->label('Titre du Rapport')
                        ->required()
                        ->maxLength(191)
                        ->disabled(fn () => $this->report && $this->report->status === ReportStatusEnum::SUBMITTED),
                    TextInput::make('theme')
                        ->label('Thème Principal')
                        ->nullable()
                        ->maxLength(191)
                        ->disabled(fn () => $this->report && $this->report->status === ReportStatusEnum::SUBMITTED),
                    Textarea::make('abstract')
                        ->label('Résumé (Abstract)')
                        ->nullable()
                        ->rows(5)
                        ->disabled(fn () => $this->report && $this->report->status === ReportStatusEnum::SUBMITTED),
                    Select::make('report_template_id')
                        ->label('Choisir un Modèle de Rapport')
                        ->options(ReportTemplate::all()->pluck('name', 'id'))
                        ->nullable()
                        ->reactive()
                        ->afterStateUpdated(function (?string $state, callable $set) {
                            if ($state) {
                                $template = ReportTemplate::find($state);
                                if ($template) {
                                    $sections = $template->sections->map(fn ($section) => [
                                        'title' => $section->title,
                                        'content' => $section->default_content,
                                        'order' => $section->order,
                                    ])->toArray();
                                    $set('sections', $sections);
                                }
                            } else {
                                $set('sections', []);
                            }
                        })
                        ->disabled(fn () => $this->report && $this->report->status === ReportStatusEnum::SUBMITTED),
                ])->columns(1),

            Section::make('Contenu du Rapport (Sections)')
                ->description('Rédigez le contenu de votre rapport section par section. Vous pouvez ajouter, réordonner ou supprimer des sections.')
                ->schema([
                        Repeater::make('sections')
                        ->label('Sections du Rapport')
                        ->schema([
                            TextInput::make('title')
                                ->label('Titre de la Section')
                                ->required()
                                ->maxLength(255),
                            RichEditor::make('content')
                                ->label('Contenu de la Section')
                                ->nullable(),
                            TextInput::make('order')
                                ->label('Ordre')
                                ->numeric()
                                ->default(0)
                                ->hidden(), // Masqué, géré par le drag & drop ou un bouton
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->defaultItems(0)
                        ->reorderableWithButtons()
                        ->disabled(fn () => $this->report && $this->report->status === ReportStatusEnum::SUBMITTED),
                    ]),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->model(Report::class)
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            Notification::make()->title('Erreur')->body('Profil étudiant non trouvé.')->danger()->send();

            return;
        }

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        if (! $activeAcademicYear) {
            Notification::make()->title('Erreur')->body('Année académique active non configurée.')->danger()->send();

            return;
        }

        // Check eligibility via middleware
        $request = request();
        $middleware = app(EnsureStudentIsEligible::class);
        $response = $middleware->handle($request, function ($req) {
            return null;
        });
        if ($response) {
            // Middleware redirected, so eligibility check failed.
            // The middleware itself should have sent a notification.
            return;
        }

        try {
            $data = $this->form->getState();

            DB::transaction(function () use ($data, $student, $activeAcademicYear) {
                if ($this->report) {
                    // Existing report (draft or needs correction)
                    $this->report->fill([
                        'title' => $data['title'],
                        'theme' => $data['theme'],
                        'abstract' => $data['abstract'],
                        'report_template_id' => $data['report_template_id'],
                        'last_modified_date' => now(),
                    ]);
                    $this->report->save();

                    // Update sections
                    $existingSectionIds = $this->report->sections->pluck('id')->toArray();
                    $submittedSectionIds = [];

                    foreach ($data['sections'] as $sectionData) {
                        $section = ReportSection::updateOrCreate(
                            ['report_id' => $this->report->id, 'title' => $sectionData['title']],
                            ['content' => $sectionData['content'], 'order' => $sectionData['order'] ?? 0]
                        );
                        $submittedSectionIds[] = $section->id;
                    }

                    // Delete removed sections
                    ReportSection::where('report_id', $this->report->id)
                        ->whereNotIn('id', $submittedSectionIds)
                        ->delete();

                    Notification::make()
                        ->title('Rapport sauvegardé en brouillon')
                        ->success()
                        ->send();

                } else {
                    // New report
                    $this->report = Report::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $activeAcademicYear->id,
                        'title' => $data['title'],
                        'theme' => $data['theme'],
                        'abstract' => $data['abstract'],
                        'status' => ReportStatusEnum::DRAFT,
                        'last_modified_date' => now(),
                        'report_template_id' => $data['report_template_id'],
                    ]);

                    foreach ($data['sections'] as $sectionData) {
                        ReportSection::create([
                            'report_id' => $this->report->id,
                            'title' => $sectionData['title'],
                            'content' => $sectionData['content'],
                            'order' => $sectionData['order'] ?? 0,
                        ]);
                    }

                    Notification::make()
                        ->title('Nouveau rapport créé en brouillon')
                        ->success()
                        ->send();
                }
            });

            // Refresh the page to update the form state and report object
            $this->redirect(static::getUrl());

        } catch (IncompleteSubmissionException $e) {
            Notification::make()
                ->title('Soumission incomplète')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (StateConflictException $e) {
            Notification::make()
                ->title('Conflit de version')
                ->body($e->getMessage().' La page va être rechargée.')
                ->danger()
                ->send();
            $this->redirect(static::getUrl()); // Force reload to get fresh data
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Erreur de validation')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur lors de la sauvegarde du rapport')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submit(): void
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student || ! $this->report) {
            Notification::make()->title('Erreur')->body('Rapport non trouvé ou profil étudiant non lié.')->danger()->send();

            return;
        }

        // Check eligibility via middleware
        $request = request();
        $middleware = app(EnsureStudentIsEligible::class);
        $response = $middleware->handle($request, function ($req) {
            return null;
        });
        if ($response) {
            return;
        }

        try {
            $data = $this->form->getState();
            $sectionsData = $data['sections'];

            // Pass the current version for optimistic locking
            $this->reportFlowService->submitReport($this->report, $sectionsData, $this->currentReportVersion);

            Notification::make()
                ->title('Rapport soumis avec succès !')
                ->body('Votre rapport a été transmis pour vérification de conformité.')
                ->success()
                ->send();

            $this->redirect(route('filament.app.pages.dashboard')); // Redirect to dashboard after submission

        } catch (IncompleteSubmissionException $e) {
            Notification::make()
                ->title('Soumission incomplète')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (StateConflictException $e) {
            Notification::make()
                ->title('Conflit de version')
                ->body($e->getMessage().' La page va être rechargée.')
                ->danger()
                ->send();
            $this->redirect(static::getUrl()); // Force reload to get fresh data
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Erreur de validation')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur lors de la soumission du rapport')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        $actions = [
            \Filament\Forms\Components\Actions\Action::make('save')
                ->label('Sauvegarder Brouillon')
                ->submit('save')
                ->color('gray')
                ->visible(fn () => ! $this->report || $this->report->status === ReportStatusEnum::DRAFT || $this->report->status === ReportStatusEnum::NEEDS_CORRECTION),
            \Filament\Forms\Components\Actions\Action::make('submit')
                ->label('Soumettre le Rapport')
                ->submit('submit')
                ->color('primary')
                ->visible(fn () => ! $this->report || $this->report->status === ReportStatusEnum::DRAFT || $this->report->status === ReportStatusEnum::NEEDS_CORRECTION),
        ];

        if ($this->report && $this->report->status === ReportStatusEnum::NEEDS_CORRECTION) {
            $actions[] = \Filament\Forms\Components\Actions\Action::make('add_correction_note')
                ->label('Ajouter Note de Correction')
                ->action(function () {
                    // Logic to add a correction note, perhaps open a modal
                    Notification::make()->title('Fonctionnalité à implémenter')->body('Ajout de note de correction.')->info()->send();
                })
                ->color('warning');
        }

        return $actions;
    }
}
