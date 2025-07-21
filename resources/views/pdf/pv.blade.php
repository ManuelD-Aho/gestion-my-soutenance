<!DOCTYPE html>
<html>
<head>
    <title>Procès-Verbal de Soutenance</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 18pt; margin-bottom: 5px; }
        .header p { font-size: 12pt; margin-top: 0; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 14pt; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        .jury-members ul { list-style-type: none; padding: 0; }
        .jury-members li { margin-bottom: 5px; }
        .report-details { margin-left: 20px; }
        .signature-block { margin-top: 50px; text-align: center; }
        .signature-block div { display: inline-block; width: 45%; margin: 0 2.5%; vertical-align: top; }
        .signature-line { border-top: 1px solid #000; padding-top: 5px; margin-top: 30px; }
        .footer { text-align: center; font-size: 9pt; margin-top: 50px; }
    </style>
</head>
<body>
<div class="header">
    <h1>PROCÈS-VERBAL DE LA COMMISSION DE SOUTENANCE</h1>
    <p>Session : {{ $pv->commissionSession->name }}</p>
    <p>Date : {{ $pv->commissionSession->start_date->format('d/m/Y') }}</p>
</div>

<div class="section">
    <h2>Composition de la Commission</h2>
    <p><strong>Président :</strong> {{ $pv->commissionSession->president->first_name }} {{ $pv->commissionSession->president->last_name }}</p>
    <div class="jury-members">
        <p><strong>Membres :</strong></p>
        <ul>
            @foreach($pv->commissionSession->teachers as $teacher)
                @if($teacher->id !== $pv->commissionSession->president->id)
                    <li>- {{ $teacher->first_name }} {{ $teacher->last_name }}</li>
                @endif
            @endforeach
        </ul>
    </div>
</div>

<div class="section">
    <h2>Rapports Évalués et Décisions</h2>
    @foreach($pv->commissionSession->reports as $report)
        <div class="report-details">
            <h3>Rapport : {{ $report->title }} (ID: {{ $report->report_id }})</h3>
            <p><strong>Étudiant :</strong> {{ $report->student->first_name }} {{ $report->student->last_name }} (Numéro : {{ $report->student->student_card_number }})</p>
            @php
                $finalDecision = app(\App\Services\CommissionFlowService::class)->calculateFinalDecisionForReport($report, $pv->commissionSession);
            @endphp
            <p><strong>Décision Finale :</strong> {{ $finalDecision ? $finalDecision->value : 'Non décidée' }}</p>
            <p><strong>Commentaires Généraux de la Commission :</strong></p>
            <ul>
                @foreach($report->votes()->where('commission_session_id', $pv->commissionSession->id)->get() as $vote)
                    <li>{{ $vote->teacher->first_name }} {{ $vote->teacher->last_name }} ({{ $vote->voteDecision->name }}): {{ $vote->comment }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>

<div class="section">
    <h2>Contenu Officiel du Procès-Verbal</h2>
    <pre>{{ $pv->content }}</pre>
</div>

<div class="signature-block">
    <div>
        <p>Fait à [Lieu], le {{ $pv->created_at->format('d/m/Y') }}</p>
        <div class="signature-line">Le Président de la Commission</div>
        <p>{{ $pv->commissionSession->president->first_name }} {{ $pv->commissionSession->president->last_name }}</p>
    </div>
    <div>
        <p> </p>
        <div class="signature-line">Le Rédacteur du PV</div>
        <p>{{ $pv->author->name }}</p>
    </div>
</div>

<div class="footer">
    <p>Document généré par GestionMySoutenance - ID PV : {{ $pv->pv_id }} - Version : {{ $pv->version }}</p>
    <p>Ce document est authentifié par son hash : {{ $pv->file_hash }}</p>
</div>
</body>
</html>
