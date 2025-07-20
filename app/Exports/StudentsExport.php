<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class StudentsExport implements FromCollection
{
    public function collection()
    {
        // Return collection of students
    }
}
