<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['data' => $request->user()->load('customerProfile')]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'code_phone' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'sexe' => 'nullable|string|max:10',
            'date_naissance' => 'nullable|date',
            'profil' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('profil')) {
            if ($user->profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profil);
            }
            $validated['profil'] = $request->file('profil')->store('profiles', 'public');
        } else {
            unset($validated['profil']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'data' => $user->load('customerProfile')
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }

    public function updateCni(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'cni_number' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:255',
            // Note: For actual file uploads, we'd handle 'file|image' etc. 
            // Here we assume path might be sent or handled via another endpoint if base64/files.
            // Let's assume standard file uploads for recto/verso if present.
            'cni_recto' => 'nullable|image|max:5120',
            'cni_verso' => 'nullable|image|max:5120',
        ]);

        $profile = $user->customerProfile()->firstOrCreate([]);
        
        $dataToUpdate = [
            'cni_number' => $validated['cni_number'] ?? $profile->cni_number,
            'adresse' => $validated['adresse'] ?? $profile->adresse,
            'ville' => $validated['ville'] ?? $profile->ville,
            'pays' => $validated['pays'] ?? $profile->pays,
            'nationalite' => $validated['nationalite'] ?? $profile->nationalite,
        ];

        $cniChanged = false;

        if ($request->hasFile('cni_recto')) {
            $dataToUpdate['cni_recto_path'] = $request->file('cni_recto')->store('cni', 'public');
            $cniChanged = true;
        }
        if ($request->hasFile('cni_verso')) {
            $dataToUpdate['cni_verso_path'] = $request->file('cni_verso')->store('cni', 'public');
            $cniChanged = true;
        }
        
        if ($request->filled('cni_number') && $request->cni_number !== $profile->cni_number) {
            $cniChanged = true;
        }

        // Si une information sensible de la CNI a changé, on réinitialise la vérification
        if ($cniChanged) {
            $dataToUpdate['cni_verified'] = false;
        }

        $profile->update($dataToUpdate);

        return response()->json([
            'message' => 'Informations complémentaires mises à jour.',
            'data' => $user->fresh()->load('customerProfile')
        ]);
    }
}
