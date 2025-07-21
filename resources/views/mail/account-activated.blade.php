<!DOCTYPE html>
<html>
<head>
    <title>Votre compte a été activé !</title>
</head>
<body>
<h1>Bienvenue sur GestionMySoutenance !</h1>
<p>Votre compte a été créé avec succès.</p>
<p>Voici vos identifiants de connexion temporaires :</p>
<ul>
    <li><strong>Email :</strong> {{ $user->email }}</li>
    <li><strong>Mot de passe temporaire :</strong> {{ $password }}</li>
</ul>
<p>Nous vous recommandons de changer votre mot de passe dès votre première connexion.</p>
<p>Cliquez ici pour vous connecter : <a href="{{ url('/login') }}">{{ url('/login') }}</a></p>
<p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
<p>Cordialement,</p>
<p>L'équipe GestionMySoutenance</p>
</body>
</html>
