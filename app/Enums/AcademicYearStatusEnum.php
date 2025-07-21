<?php

    namespace App\Enums;

    enum AcademicYearStatusEnum: string
    {
        case ACTIVE = 'Active';
        case ARCHIVED = 'Archivée';
        case PLANNED = 'Planifiée';
    }