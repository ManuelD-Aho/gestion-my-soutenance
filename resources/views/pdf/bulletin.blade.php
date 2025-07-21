<!DOCTYPE html>
<html>
<head>
    <title>Bulletin de Notes</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .student-info { margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .footer { text-align: center; font-size: 8pt; margin-top: 50px; }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.1);
            z-index: -1000;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="watermark">PROVISOIRE</div>

<div class="header">
    <h1>BULLETIN DE NOTES PROVISOIRE</h1>
    <p>Année Académique : {{ $academicYear->label }}</p>
</div>

<div class="student-info">
    <h3>Informations Étudiant :</h3>
    <p><strong>Nom :</strong> {{ $student->last_name }}</p>
    <p><strong>Prénom :</strong> {{ $student->first_name }}</p>
    <p><strong>Numéro Carte :</strong> {{ $student->student_card_number }}</p>
    <p><strong>Niveau d'Étude :</strong> {{ $studyLevel->name }}</p>
</div>

<h3>Détail des Notes :</h3>
<table>
    <thead>
    <tr>
        <th>UE</th>
        <th>ECUE</th>
        <th>Crédits</th>
        <th>Note</th>
        <th>Décision</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($grades as $grade)
        <tr>
            <td>{{ $grade['ue_name'] }}</td>
            <td>{{ $grade['ecue_name'] }}</td>
            <td>{{ $grade['credits'] }}</td>
            <td>{{ $grade['score'] }}</td>
            <td>{{ $grade['decision'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>Généré le : {{ now()->format('d/m/Y H:i:s') }}</p>
    <p>Ceci est un document provisoire et non officiel. Il ne peut être utilisé à des fins administratives formelles.</p>
</div>
</body>
</html>
