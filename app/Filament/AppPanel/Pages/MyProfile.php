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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.app-panel.pages.my-profile';

    protected static ?string $navigationLabel = 'Mon Profil';

    public ?array $data = [];

    public ?User $user;

    public ?\Illuminate\Database\Eloquent\Model $profile = null;

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user) {
            // Redirect or handle unauthenticated user
            abort(403, 'User not authenticated.');
        }

        $this->user = $user;
        $this->profile = $this->user->student ?? $this->user->teacher ?? $this->user->administrativeStaff;

        $profileData = [];
        if ($this->profile) {
            $profileData = $this->profile->toArray();
        }

        $this->data = array_merge($this->user->toArray(), $profileData);

        // Load specific profile data based on role
        if ($this->isStudent() && $this->user->student) {
            $this->data = array_merge($this->data, $this->user->student->toArray());
        } elseif ($this->isTeacher() && $this->user->teacher) {
            $this->data = array_merge($this->data, $this->user->teacher->toArray());
        } elseif ($this->isAdministrativeStaff() && $this->user->administrativeStaff) {
            $this->data = array_merge($this->data, $this->user->administrativeStaff->toArray());
        }
    }

    public function getUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return Auth::user();
    }

    public function isStudent(): bool
    {
        $user = $this->getUser();
        return $user && !is_null($user->student);
    }

    public function isTeacher(): bool
    {
        $user = $this->getUser();
        return $user && !is_null($user->teacher);
    }

    public function isAdministrativeStaff(): bool
    {
        $user = $this->getUser();
        return $user && !is_null($user->administrativeStaff);
    }

    protected function getFormSchema(): array
    {
        $user = $this->getUser();

        if (!$user) {
            return [];
        }

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
                        ->unique('users', 'email')
                        ->rule(fn () => $user instanceof User ? 'unique:users,email,' . $user->id : null)
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
                        ->requiredWith('password')
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
                        ->requiredWith('password')
                        ->same('password')
                        ->autocomplete('new-password'),
                ])->columns(2),
        ];

        if ($profileType && $this->profile) {
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
                        ->maxLength(255)
                        ->nullable()
                        ->unique(
                            table: $profileType === 'teacher' ? Teacher::class : AdministrativeStaff::class,
                            column: 'professional_email'
                        )
                        ->rule(fn () => $this->profile ? 'unique:' . ($profileType === 'teacher' ? 'teachers' : 'administrative_staff') . ',professional_email,' . $this->profile->id : null),
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

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            if(!$this->user) {
                return;
            }

            // Update User model
            $this->user->name = $data['name'];
            $this->user->email = $data['email'];

            if (! empty($data['password'])) {
                $this->user->password = Hash::make($data['password']);
            }

            $this->user->save();

            // Update associated profile model (Student, Teacher, AdministrativeStaff)
            if ($this->profile) {
                $profileData = array_intersect_key($data, array_flip($this->profile->getFillable()));
                $this->profile->fill($profileData);
                $this->profile->save();
            }

            // Handle profile photo upload
            if (isset($data['profile_photo_path'])) {
                $this->user->updateProfilePhoto($data['profile_photo_path']);
            } elseif ($this->user->profile_photo_path) {
                $this->user->deleteProfilePhoto();
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
