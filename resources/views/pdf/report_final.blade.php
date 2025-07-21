<!DOCTYPE html>
<html>
<head>
    <title>Rapport de Soutenance Final</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12pt; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 24pt; margin-bottom: 10px; }
        .header p { font-size: 14pt; margin-top: 0; }
        .info-block { margin-bottom: 30px; border: 1px solid #eee; padding: 15px; background-color: #f9f9f9; }
        .section { margin-bottom: 30px; }
        .section h2 { font-size: 18pt; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-bottom: 15px; }
        .footer { text-align: center; font-size: 10pt; margin-top: 50px; color: #555; }
    </style>
</head>
<body>
<div class="header">
    <h1>RAPPORT DE SOUTENANCE FINAL</h1>
    <p>{{ $report->title }}</p>
    <p>Thème : {{ $report->theme }}</p>
</div>

<div class="info-block">
    <h3>Informations Générales</h3>
    <p><strong>Étudiant :</strong> {{ $report->student->first_name }} {{ $report->student->last_name }} (Numéro : {{ $report->student->student_card_number }})</p>
    <p><strong>Année Académique :</strong> {{ $report->academicYear->label }}</p>
    <p><strong>Date de Soumission :</strong> {{ $report->submission_date->format('d/m/Y H:i:s') }}</p>
    <p><strong>Statut Final :</strong> {{ $report->status->value }}</p>
    <p><strong>Nombre de Pages :</strong> {{ $report->page_count }}</p>
</div>

<div class="section">
    <h2>Résumé (Abstract)</h2>
    <p>{{ $report->abstract }}</p>
</div>

@foreach($report->sections()->orderBy('order')->get() as $section)
    <div class="section">
        <h2>{{ $section->title }}</h2>
        <div>{!! $section->content !!}</div> {{-- Utiliser {!! !!} pour afficher le HTML de l'éditeur riche --}}
    </div>
@endforeach

<div class="footer">
    <p>Document généré par GestionMySoutenance - ID Rapport : {{ $report->report_id }}</p>
    <p>Version du document : {{ $report->version }}</p>
    <p>Authentifié par son hash : {{ $report->file_hash }}</p>
    <p>Date de génération du PDF : {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
</body>
</html>
