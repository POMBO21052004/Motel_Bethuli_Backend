<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Traits\NotifiesAdmins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EntrepriseController extends Controller
{
    use NotifiesAdmins;

    public function index(Request $request)
    {
        $query = Entreprise::orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 10), 9999);
        $entreprises = $query->paginate($perPage);
        return response()->json(['data' => $entreprises]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'logo' => 'nullable|image|max:2048',
            'is_actif' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('entreprises', 'public');
        }

        $entreprise = Entreprise::create($validated);

        $this->notifyAdmins(
            'Nouvelle Entreprise',
            "L'entreprise '{$entreprise->nom}' a été créée.",
            'success',
            "/entreprises/{$entreprise->id}"
        );

        return response()->json([
            'message' => 'Entreprise créée avec succès',
            'data' => $entreprise
        ], 201);
    }

    public function show(string $id)
    {
        $entreprise = Entreprise::with(['participants.sessions.formation'])->findOrFail($id);
        return response()->json(['data' => $entreprise]);
    }

    public function update(Request $request, string $id)
    {
        $entreprise = Entreprise::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'logo' => 'nullable|image|max:2048',
            'is_actif' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            if ($entreprise->logo) {
                Storage::disk('public')->delete($entreprise->logo);
            }
            $validated['logo'] = $request->file('logo')->store('entreprises', 'public');
        }

        $entreprise->update($validated);

        $this->notifyAdmins(
            'Entreprise modifiée',
            "L'entreprise '{$entreprise->nom}' a été mise à jour.",
            'info',
            "/entreprises/{$entreprise->id}"
        );

        return response()->json([
            'message' => 'Entreprise mise à jour avec succès',
            'data' => $entreprise
        ]);
    }

    public function destroy(string $id)
    {
        $entreprise = Entreprise::findOrFail($id);

        if ($entreprise->logo) {
            Storage::disk('public')->delete($entreprise->logo);
        }

        $nom = $entreprise->nom;
        $entreprise->delete();

        $this->notifyAdmins(
            'Entreprise supprimée',
            "L'entreprise '{$nom}' a été supprimée.",
            'warning'
        );

        return response()->json([
            'message' => 'Entreprise supprimée avec succès'
        ]);
    }
}
