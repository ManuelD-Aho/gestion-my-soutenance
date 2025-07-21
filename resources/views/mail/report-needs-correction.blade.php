<!DOCTYPE html>
<html>
<head>
    <title>Votre rapport de soutenance nécessite des corrections</title>
</head>
<body>
<h1>Rapport de Soutenance - Corrections Requises</h1>
<p>Cher(e) {{ $report->student->first_name }} {{ $report->student->last_name }},</p>
<p>Votre rapport de soutenance intitulé "<strong>{{ $report->title }}</strong>" (ID: {{ $report->report_id }}) a été examiné par le service de conformité.</p>
<p>Des corrections sont nécessaires avant que votre rapport puisse être transmis à la commission d'évaluation.</p>
<p>Veuillez vous connecter à votre espace sur GestionMySoutenance pour consulter les commentaires détaillés et apporter les modifications requises :</p>
<p><a href="{{ url('/app/reports/' . $report->id . '/edit') }}">{{ url('/app/reports/' . $report->id . '/edit') }}</a></p>
<p>Commentaires de l'évaluateur :</p>
<pre>{{ $comments }}</pre>
<p>Nous vous invitons à effectuer ces corrections dans les plus brefs délais et à re-soumettre votre rapport.</p>
<p>Cordialement,</p>
<p>L'équipe GestionMySoutenance</p>
</body>
</html>
