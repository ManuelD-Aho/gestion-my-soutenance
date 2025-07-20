<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Enseignant;
use App\Models\AdministrativeStaff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementService
{
    protected UniqueIdGeneratorService $uniqueIdGeneratorService;

    public function __construct(UniqueIdGeneratorService $uniqueIdGeneratorService)
    {
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
    }

    public function createUserWithProfile(array $userData, string $profileType, array $profileData)
    {
        $user = User::create([
            'user_id' => $this->uniqueIdGeneratorService->generate('USR', date('Y')),
            'name' => $userData['name'] ?? ($profileData['first_name'] . ' ' . $profileData['last_name']),
            'email' => $userData['email'],
            'password' => Hash::make($userData['password'] ?? Str::random(10)),
            'status' => $userData['status'] ?? 'active',
            'email_verified_at' => now(), // Or null if email verification is required
        ]);

        $profileData['user_id'] = $user->id;

        switch ($profileType) {
            case 'student':
                Student::create($profileData);
                $user->assignRole('Etudiant');
                break;
            case 'teacher':
                Enseignant::create($profileData);
                $user->assignRole('Enseignant');
                break;
            case 'administrative_staff':
                AdministrativeStaff::create($profileData);
                $user->assignRole('Personnel Administratif');
                break;
        }

        return $user;
    }

    public function activateStudentAccount(Student $student): User
    {
        if (!$student->user) {
            $password = Str::random(10);
            $user = User::create([
                'user_id' => $this->uniqueIdGeneratorService->generate('ETU', date('Y')),
                'name' => $student->first_name . ' ' . $student->last_name,
                'email' => $student->email_contact_secondaire ?? 'default@example.com', // Ensure email is set
                'password' => Hash::make($password),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Etudiant');
            $student->user_id = $user->id;
            $student->save();

            // Dispatch email with password
            // Mail::to()->send(new AccountActivatedMail(, ));

            return $user;
        }
        return $student->user;
    }
}
