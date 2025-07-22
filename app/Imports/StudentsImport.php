<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Student;
// Ajouter
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class StudentsImport implements ToCollection, WithHeadingRow
{
    protected array $mapping;

    protected User $importer;

    protected UserManagementService $userManagementService;

    protected array $results = ['success' => 0, 'failed' => 0, 'errors' => []];

    public function __construct(array $mapping, User $importer)
    {
        $this->mapping = $mapping;
        $this->importer = $importer;
        $this->userManagementService = app(UserManagementService::class);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $mappedData = [];
            foreach ($this->mapping as $fileColumn => $modelField) {
                if (isset($row[$fileColumn])) {
                    $mappedData[$modelField] = $row[$fileColumn];
                }
            }

            try {
                // Validation des données pour la création du profil Student
                $validator = Validator::make($mappedData, [
                    'first_name' => ['required', 'string', 'max:255'],
                    'last_name' => ['required', 'string', 'max:255'],
                    'email_contact_personnel' => ['required', 'email', 'unique:students,email_contact_personnel'], // Unique dans la table students
                    'student_card_number' => ['required', 'string', 'unique:students,student_card_number'],
                    // Ajoutez d'autres règles de validation selon vos besoins
                ]);

                if ($validator->fails()) {
                    throw new \Exception(implode(', ', $validator->errors()->all()));
                }

                // Création du profil étudiant (sans activation de compte utilisateur ici)
                // L'activation du compte utilisateur sera faite par le RS via l'interface
                $this->userManagementService->createProfile('student', $mappedData);
                $this->results['success']++;

            } catch (Throwable $e) {
                $this->results['failed']++;
                $this->results['errors'][] = [
                    'row_data' => $row->toArray(),
                    'error_message' => $e->getMessage(),
                ];
            }
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
