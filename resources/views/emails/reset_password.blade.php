<!DOCTYPE html>
<html>
<head>
    <title>Réinitialisation de mot de passe</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;">
    <div style="max-w-md; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #0f172a; text-align: center;">Motel Bethuli</h2>
        <p style="color: #334155; font-size: 16px;">Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetLink }}" style="background-color: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                Réinitialiser le mot de passe
            </a>
        </div>
        
        <p style="color: #64748b; font-size: 14px;">Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action n'est requise.</p>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;" />
        <p style="color: #94a3b8; font-size: 12px; text-align: center;">© {{ date('Y') }} Motel Bethuli. Tous droits réservés.</p>
    </div>
</body>
</html>
