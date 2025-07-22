<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Report;
use App\Models\Student; // Ajouter
// Ajouter
use App\Models\User;
use App\Services\ReportFlowService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class ReportsImport implements ToCollection, WithHeadingRow
{
    protected array $mapping;

    protected User $importer;

    protected ReportFlowService $reportFlowService;

    protected array $results = ['success' => 0, 'failed' => 0, 'errors' => []];

    public function __construct(array $mapping, User $importer)
    {
        $this->mapping = $mapping;
        $this->importer = $importer;
        $this->reportFlowService = app(ReportFlowService::class);
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
                $validator = Validator::make($mappedData, [
                    'student_card_number' => ['required', 'exists:students,student_card_number'],
                    'title' => ['required', 'string', 'max:255'],
                    'theme' => ['required', 'string', 'max:255'],
                    // Ajoutez d'autres règles de validation
                ]);

                if ($validator->fails()) {
                    throw new \Exception(implode(', ', $validator->errors()->all()));
                }

                $student = Student::where('student_card_number', $mappedData['student_card_number'])->firstOrFail();

                // Créer un rapport en statut brouillon
                $report = Report::create([
                    'student_id' => $student->id,
                    'academic_year_id' => \App\Models\AcademicYear::getActive()->id, // Assumer une année active
                    'title' => $mappedData['title'],
                    'theme' => $mappedData['theme'],
                    'status' => \App\Enums\ReportStatusEnum::DRAFT,
                    'submission_date' => null,
                    'last_modified_date' => now(),
                    'version' => 1,
                ]);

                // Pas de soumission directe ici, juste la création du brouillon.
                // La soumission passera par le workflow normal via l'étudiant ou l'admin.

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
