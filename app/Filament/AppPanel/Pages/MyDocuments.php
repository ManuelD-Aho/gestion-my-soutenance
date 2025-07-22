<?php

    namespace App\Filament\AppPanel\Pages;

    use Filament\Pages\Page;
    use Filament\Tables\Table;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\IconColumn;
    use Filament\Tables\Actions\Action;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;
    use App\Models\Document;
    use App\Models\User;
    use App\Enums\DocumentTypeEnum;

    class MyDocuments extends Page
    {
        protected static ?string $navigationIcon = 'heroicon-o-document-text';
        protected static string $view = 'filament.app-panel.pages.my-documents';
        protected static ?string $navigationLabel = 'Mes Documents';

        public function table(Table $table): Table
        {
            /** @var User $user */
            $user = Auth::user();

            $query = Document::query();

            if ($user->hasRole('Etudiant') && $user->student) {
                $query->where(function ($q) use ($user) {
                    $q->where('related_entity_type', \App\Models\Student::class)
                      ->where('related_entity_id', $user->student->id)
                      ->orWhere(function ($q2) use ($user) {
                          $q2->where('related_entity_type', \App\Models\Report::class)
                             ->whereHas('relatedEntity', function ($q3) use ($user) {
                                 $q3->where('student_id', $user->student->id);
                             });
                      });
                });
            } elseif ($user->hasRole('Responsable Scolarite') || $user->hasRole('Agent de Conformite') || $user->hasRole('Admin')) {
                // Admin, RS, Agent de Conformité peuvent voir tous les documents pertinents
                // Pour simplifier, on affiche tous les documents pour ces rôles ici,
                // mais une logique plus fine pourrait être appliquée (ex: documents générés par eux).
            } else {
                $query->where('generated_by_user_id', $user->id); // Par défaut, seulement les documents générés par l'utilisateur
            }

            return $table
                ->query($query)
                ->columns([
                    TextColumn::make('document_id')
                        ->label('ID Document')
                        ->searchable(),
                    TextColumn::make('documentType.name')
                        ->label('Type de Document')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('relatedEntity.title') // Supposons que l'entité liée a un champ 'title'
                        ->label('Concerne')
                        ->limit(50)
                        ->tooltip(fn (Document $record): ?string => $record->relatedEntity?->title),
                    TextColumn::make('generation_date')
                        ->label('Date de Génération')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('version')
                        ->label('Version')
                        ->sortable(),
                    IconColumn::make('is_public') // Supposons une colonne is_public sur le modèle Document
                        ->label('Public')
                        ->boolean()
                        ->getStateUsing(fn (Document $record): bool => str_contains($record->file_path, 'public/')),
                ])
                ->actions([
                    Action::make('download')
                        ->label('Télécharger')
                        ->icon('heroicon-o-download')
                        ->action(function (Document $record) {
                            if (Storage::exists($record->file_path)) {
                                return Storage::download($record->file_path);
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Fichier non trouvé')
                                ->body('Le document demandé n\'existe plus sur le serveur.')
                                ->danger()
                                ->send();
                        }),
                    // Action pour visualiser le document si c'est un PDF et qu'il est public
                    Action::make('view')
                        ->label('Voir')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Document $record): string => Storage::url($record->file_path))
                        ->openUrlInNewTab()
                        ->visible(fn (Document $record): bool => str_contains($record->file_path, 'public/') && str_ends_with($record->file_path, '.pdf')),
                ])
                ->filters([
                    // Filtres pour les types de documents
                    \Filament\Tables\Filters\SelectFilter::make('document_type_id')
                        ->relationship('documentType', 'name')
                        ->label('Filtrer par Type'),
                ]);
        }
    }