<?php

    namespace App\Enums;

    enum JuryRoleEnum: string
    {
        case PRESIDENT = 'Président du Jury';
        case RAPPORTEUR = 'Rapporteur';
        case MEMBRE = 'Membre du Jury';
        case DIRECTOR = 'Directeur de Mémoire'; // Rôle spécifique pour l'encadrement
    }