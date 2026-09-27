<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Votre Attestation de Formation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2f8f52;">Bonjour {{ $participant->prenom }} {{ $participant->nom }},</h2>
        
        <p>Nous avons le plaisir de vous informer que votre attestation pour la formation <strong>{{ $sessionData->formation->intitule }}</strong> est prête.</p>
        
        <p>Vous trouverez votre attestation en pièce jointe de cet e-mail au format PDF.</p>
        
        <p>Nous vous remercions pour votre confiance et espérons vous revoir très bientôt lors de nos prochaines sessions de formation.</p>
        
        <p>Cordialement,</p>
        <p><strong>L'équipe FEC (Free Engineering Consultancy)</strong><br>
        <a href="mailto:infos@feconsultancy.com">infos@feconsultancy.com</a></p>
    </div>
</body>
</html>
