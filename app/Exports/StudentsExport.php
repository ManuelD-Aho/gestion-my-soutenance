<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Student::select(
            'student_card_number',
            'first_name',
            'last_name',
            'email_contact_personnel',
            'phone',
            'address',
            'city',
            'postal_code',
            'nationality'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Numéro Carte Étudiant',
            'Prénom',
            'Nom',
            'Email Personnel',
            'Téléphone',
            'Adresse',
            'Ville',
            'Code Postal',
            'Nationalité',
        ];
    }
}
