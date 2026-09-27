<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue sur la plateforme</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>
    
    <p>Votre compte administrateur a été créé avec succès sur notre plateforme.</p>
    
    <p>Voici vos informations de connexion générées automatiquement :</p>
    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <ul style="list-style-type: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 10px;"><strong>Email :</strong> {{ $user->email }}</li>
            <li><strong>Mot de passe provisoire :</strong> <span style="font-family: monospace; font-size: 1.1em;">{{ $password }}</span></li>
        </ul>
    </div>

    <p>Vous pouvez vous connecter en cliquant sur le lien ci-dessous :</p>
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $loginUrl }}" style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Se connecter à la plateforme
        </a>
    </p>

    <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin-top: 30px;">
        <p style="margin: 0;"><strong>Conseil de sécurité :</strong> Nous vous recommandons vivement de modifier votre mot de passe dès votre première connexion pour garantir la sécurité de votre compte.</p>
    </div>
    
    <p style="margin-top: 40px; color: #6b7280; font-size: 0.9em;">
        Cordialement,<br>
        L'équipe d'administration
    </p>
</body>
</html>
