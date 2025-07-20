<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    public function model(array $row)
    {
        // Map row to Student model
    }
}
