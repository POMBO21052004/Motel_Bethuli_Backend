<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use App\Models\CustomerProfile;
use App\Enums\UserRole;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouveau client.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nom'      => 'required|string|max:255',
            'prenom'   => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => UserRole::CLIENT, // Rôle par défaut
        ]);

        CustomerProfile::create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('customerProfile'),
        ], 201);
    }

    /**
     * Connexion.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!$user->actif) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte a été désactivé.'],
            ]);
        }

        // Vérifier l'exigence d'OTP : Inactif depuis 7 jours (Optionnel pour Motel Bethuli, mais on suit le modèle Delico)
        $latestActivity = $user->last_login ?? $user->created_at;
        $daysInactive = $latestActivity ? Carbon::now()->diffInDays($latestActivity) : 0;
        
        // Pour un hôtel, on peut forcer l'OTP à la première connexion d'un admin ou si inactif
        // Ici, simplifions en disant que si inactif > 7 jours, on demande l'OTP.
        $requiresOtp = $daysInactive >= 7;

        if ($requiresOtp) {
            $this->generateAndSendOtp($user->email);
            return response()->json([
                'message' => 'Un code de vérification vous a été envoyé par email.',
                'require_otp' => true,
                'email' => $user->email
            ], 202);
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
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expiré.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->actif) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
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

    private function generateAndSendOtp($email)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        Mail::to($email)->send(new OtpMail($code));
    }

    private function performLogin(User $user)
    {
        $user->last_login = now();
        $user->save();

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('customerProfile'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('customerProfile')
        ]);
    }
}
