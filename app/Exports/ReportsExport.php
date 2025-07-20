<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ReportsExport implements FromCollection
{
    public function collection()
    {
        // Return collection of reports
    }
}
