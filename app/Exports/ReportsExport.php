<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Report::with('student', 'academicYear', 'status')
            ->select(
                'report_id',
                'title',
                'theme',
                'student_id',
                'academic_year_id',
                'status',
                'submission_date',
                'last_modified_date'
            )
            ->get()
            ->map(function ($report) {
                return [
                    'ID Rapport' => $report->report_id,
                    'Titre' => $report->title,
                    'Thème' => $report->theme,
                    'Numéro Carte Étudiant' => $report->student->student_card_number ?? 'N/A',
                    'Nom Étudiant' => ($report->student->first_name ?? '').' '.($report->student->last_name ?? ''),
                    'Année Académique' => $report->academicYear->label ?? 'N/A',
                    'Statut' => $report->status->value ?? 'N/A',
                    'Date Soumission' => $report->submission_date ? $report->submission_date->format('Y-m-d H:i:s') : 'N/A',
                    'Dernière Modification' => $report->last_modified_date ? $report->last_modified_date->format('Y-m-d H:i:s') : 'N/A',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID Rapport',
            'Titre',
            'Thème',
            'Numéro Carte Étudiant',
            'Nom Étudiant',
            'Année Académique',
            'Statut',
            'Date Soumission',
            'Dernière Modification',
        ];
    }
}
