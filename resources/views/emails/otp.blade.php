<!DOCTYPE html>
<html>
<head>
    <title>Code de vérification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;">
    <div style="max-w-md; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #0f172a; text-align: center;">Motel Bethuli</h2>
        <p style="color: #334155; font-size: 16px;">Voici votre code de vérification à usage unique :</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="background-color: #f59e0b; color: white; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 24px; letter-spacing: 4px;">
                {{ $code }}
            </span>
        </div>
        
        <p style="color: #64748b; font-size: 14px;">Ce code expirera dans 10 minutes. Ne le partagez avec personne.</p>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;" />
        <p style="color: #94a3b8; font-size: 12px; text-align: center;">© {{ date('Y') }} Motel Bethuli. Tous droits réservés.</p>
    </div>
</body>
</html>
