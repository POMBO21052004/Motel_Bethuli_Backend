<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Connexion de l'utilisateur - gère l'OTP si nécessaire
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Vérifier les identifiants
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.',
                'errors' => ['email' => ['Email ou mot de passe incorrect.']]
            ], 401);
        }

        // Vérifier si le compte est actif
        if (!$user->actif) {
            return response()->json([
                'message' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ], 403);
        }

        // Vérifier l'exigence d'OTP : Non vérifié (première connexion) ou inactif depuis 7 jours
        $latestActivity = $user->last_login ?? $user->created_at;
        $daysInactive = $latestActivity ? Carbon::now()->diffInDays($latestActivity) : 0;
        $requiresOtp = !$user->is_verified || $daysInactive >= 7;

        if ($requiresOtp) {
            $this->generateAndSendOtp($user->email);
            return response()->json([
                'message' => 'Un code de vérification vous a été envoyé par email.',
                'require_otp' => true,
                'email' => $user->email // renvoyé pour faciliter la suite sur le front
            ], 202); // 202 Accepted indique que la requête est reçue mais pas complétée
        }

        return $this->performLogin($user);
    }

    /**
     * Valide le code OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string|size:6',
        ]);

        $otp = Otp::where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return response()->json([
                'message' => 'Code invalide ou expiré.',
                'errors' => ['code' => ['Code invalide ou expiré.']]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->actif) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        // Marquer comme vérifié si c'était la première connexion
        if (!$user->is_verified) {
            $user->is_verified = true;
            $user->save();
        }

        // Nettoyer l'OTP
        $otp->delete();

        return $this->performLogin($user);
    }

    /**
     * Renvoyer un code OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $this->generateAndSendOtp($request->email);

        return response()->json([
            'message' => 'Un nouveau code vous a été envoyé.'
        ], 200);
    }

    /**
     * Génère l'OTP et l'envoie par email
     */
    private function generateAndSendOtp($email)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        Mail::to($email)->send(new OtpMail($code));
    }

    /**
     * Connecte l'utilisateur et génère le token Sanctum
     */
    private function performLogin(User $user)
    {
        // Supprimer les anciens tokens (un seul token actif par utilisateur)
        $user->tokens()->delete();

        // Créer un token avec les abilities basées sur le rôle
        // Expiration : 8 heures d'activité max (sécurité renforcée)
        $abilities = $this->getAbilitiesForRole($user->role);
        $expiresAt = now()->addHours(8);
        $token = $user->createToken(
            'auth_token',
            $abilities,
            $expiresAt
        )->plainTextToken;

        // Mise à jour de la dernière connexion
        $user->update(['last_login' => now()]);

        return response()->json([
            'message'      => 'Connexion réussie.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_at'   => $expiresAt->toIso8601String(), // retourné au frontend
            'user' => [
                'id'         => $user->id,
                'nom'        => $user->nom,
                'prenom'     => $user->prenom,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'code_phone' => $user->code_phone,
                'sexe'       => $user->sexe,
                'profil'     => $user->profil,
                'role'       => $user->role,
                'actif'      => $user->actif,
                'last_login' => clone $user->last_login,
                'created_at' => $user->created_at,
            ]
        ], 200);
    }

    /**
     * Retourner les informations de l'utilisateur connecté
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'phone' => $user->phone,
                'code_phone' => $user->code_phone,
                'sexe' => $user->sexe,
                'profil' => $user->profil,
                'role' => $user->role,
                'actif' => $user->actif,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
            ]
        ], 200);
    }

    /**
     * Déconnexion - révocation du token courant
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ], 200);
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'code_phone' => 'nullable|string|max:10',
            'sexe' => 'nullable|in:M,F',
        ]);

        $profilPath = $user->profil;
        if ($request->hasFile('profil')) {
            $request->validate(['profil' => 'image|max:2048']);
            if ($user->profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profil);
            }
            $profilPath = $request->file('profil')->store('profils', 'public');
        }

        $user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'phone' => $request->phone,
            'code_phone' => $request->code_phone,
            'sexe' => $request->sexe,
            'profil' => $profilPath,
        ]);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user
        ], 200);
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors' => ['current_password' => ['Le mot de passe actuel est incorrect.']]
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.'
        ], 200);
    }

    /**
     * Définir les capacités (abilities) du token selon le rôle
     */
    private function getAbilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['*'], // Accès total
            'gestionnaire' => [
                'formations:read',
                'formations:write',
                'sessions:read',
                'sessions:write',
                'participants:read',
                'participants:write',
                'attestations:read',
                'attestations:generate',
                'payements:read',
                'payements:write',
                'entreprises:read',
            ],
            default => [],
        };
    }
}
