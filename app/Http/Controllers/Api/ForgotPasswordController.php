<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Envoyer le lien de réinitialisation de mot de passe.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Recherche discrète (anti-enumeration)
        $userExists = User::where('email', $email)->exists();

        if ($userExists) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'email'      => $email,
                    'token'      => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');
            $resetLink   = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($email);

            Mail::to($email)->send(new ResetPasswordMail($resetLink));
        }

        // Délai artificiel
        usleep(300_000);

        return response()->json([
            'message' => 'Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation dans quelques instants.'
        ], 200);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
            return response()->json([
                'message' => 'Ce token de réinitialisation est invalide.',
                'errors' => ['token' => ['Token invalide ou expiré.']]
            ], 422);
        }

        if (Carbon::parse($resetToken->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'message' => 'Ce token de réinitialisation a expiré.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.'
        ], 200);
    }
}
