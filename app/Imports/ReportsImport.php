<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

class ReportsImport implements ToModel
{
    public function model(array $row)
    {
        // Map row to Report model
    }
}
