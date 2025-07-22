<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Pages;

use App\Enums\GenderEnum;
use App\Models\AdministrativeStaff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\UserManagementService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // Assurez-vous que ce service existe

class MyProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.app-panel.pages.my-profile';

    protected static ?string $navigationLabel = 'Mon Profil';

    public ?array $data = [];

    public User $user;

    public ?\Illuminate\Database\Eloquent\Model $profile = null;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->profile = $this->user->student ?? $this->user->teacher ?? $this->user->administrativeStaff;

        $profileData = [];
        if ($this->profile) {
            $profileData = $this->profile->toArray();
        }

        $this->form->fill(array_merge($this->user->toArray(), $profileData));
    }

    protected function getFormSchema(): array
    {
        $user = Auth::user();
        $profileType = null;
        if ($user->student) {
            $profileType = 'student';
        } elseif ($user->teacher) {
            $profileType = 'teacher';
        } elseif ($user->administrativeStaff) {
            $profileType = 'administrative_staff';
        }

        $schema = [
            Section::make('Informations du Compte')
                ->description('Gérez les informations de votre compte utilisateur.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nom d\'affichage')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Adresse Email (Login)')
                        ->email()
                        ->required()
                        ->unique('users', 'email', ignoreRecord: $user)
                        ->maxLength(255),
                    FileUpload::make('profile_photo_path')
                        ->label('Photo de profil')
                        ->avatar()
                        ->image()
                        ->maxSize(1024)
                        ->disk('public')
                        ->directory('profile-photos'),
                ])->columns(2),

            Section::make('Changer le Mot de Passe')
                ->description('Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé.')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Mot de passe actuel')
                        ->password()
                        ->required()
                        ->currentPassword()
                        ->autocomplete('current-password'),
                    TextInput::make('password')
                        ->label('Nouveau mot de passe')
                        ->password()
                        ->required()
                        ->rule(Password::default())
                        ->autocomplete('new-password'),
                    TextInput::make('password_confirmation')
                        ->label('Confirmer le mot de passe')
                        ->password()
                        ->required()
                        ->same('password')
                        ->autocomplete('new-password'),
                ])->columns(2),
        ];

        if ($profileType) {
            $profileSchema = [
                Section::make('Informations Personnelles')
                    ->description('Mettez à jour vos informations personnelles.')
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(191),
                        DatePicker::make('date_of_birth')
                            ->label('Date de naissance')
                            ->nullable(),
                        TextInput::make('place_of_birth')
                            ->label('Lieu de naissance')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('country_of_birth')
                            ->label('Pays de naissance')
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('nationality')
                            ->label('Nationalité')
                            ->maxLength(50)
                            ->nullable(),
                        Select::make('gender')
                            ->label('Genre')
                            ->options(GenderEnum::class)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Coordonnées et Contact d\'Urgence')
                    ->description('Mettez à jour vos coordonnées et informations de contact d\'urgence.')
                    ->schema([
                        TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('postal_code')
                            ->label('Code Postal')
                            ->maxLength(20)
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Téléphone Personnel')
                            ->tel()
                            ->maxLength(20)
                            ->nullable(),
                        TextInput::make('secondary_email')
                            ->label('Email Secondaire')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('emergency_contact_name')
                            ->label('Contact d\'Urgence (Nom)')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('emergency_contact_phone')
                            ->label('Contact d\'Urgence (Téléphone)')
                            ->tel()
                            ->maxLength(20)
                            ->nullable(),
                        TextInput::make('emergency_contact_relation')
                            ->label('Contact d\'Urgence (Relation)')
                            ->maxLength(50)
                            ->nullable(),
                    ])->columns(2),
            ];

            if ($profileType === 'teacher' || $profileType === 'administrative_staff') {
                $professionalFields = [
                    TextInput::make('professional_phone')
                        ->label('Téléphone Professionnel')
                        ->tel()
                        ->maxLength(20)
                        ->nullable(),
                    TextInput::make('professional_email')
                        ->label('Email Professionnel')
                        ->email()
                        ->unique(
                            $profileType === 'teacher' ? Teacher::class : AdministrativeStaff::class,
                            'professional_email',
                            ignoreRecord: $this->profile
                        )
                        ->maxLength(255)
                        ->nullable(),
                ];
                if ($profileType === 'administrative_staff') {
                    $professionalFields[] = DatePicker::make('service_assignment_date')
                        ->label('Date d\'affectation au service')
                        ->nullable();
                    $professionalFields[] = TextInput::make('key_responsibilities')
                        ->label('Responsabilités Clés')
                        ->nullable();
                }
                $profileSchema[] = Section::make('Informations Professionnelles')
                    ->description('Mettez à jour vos informations professionnelles.')
                    ->schema($professionalFields)
                    ->columns(2);
            }
            $schema = array_merge($schema, $profileSchema);
        }

        return $schema;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->model($this->user)
            ->statePath('data');
    }

    public function save(UserManagementService $userManagementService): void
    {
        try {
            $data = $this->form->getState();

            // Update User model
            $this->user->name = $data['name'];
            $this->user->email = $data['email'];

            if (! empty($data['password'])) {
                $this->user->password = Hash::make($data['password']);
            }

            if (isset($data['profile_photo_path'])) {
                $this->user->updateProfilePhoto($data['profile_photo_path']);
            } else {
                // Handle case where photo is removed
                if ($this->user->profile_photo_path && ! isset($data['profile_photo_path'])) {
                    $this->user->deleteProfilePhoto();
                }
            }

            $this->user->save();

            // Update associated profile model (Student, Teacher, AdministrativeStaff)
            if ($this->profile) {
                $profileData = array_intersect_key($data, $this->profile->getFillable());
                $this->profile->fill($profileData);
                $this->profile->save();
            }

            Notification::make()
                ->title('Profil mis à jour avec succès')
                ->success()
                ->send();

            // Re-authenticate user to update session data if email/name changed
            Auth::login($this->user->fresh());

        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Erreur de validation')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur lors de la mise à jour du profil')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
