<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class ReceptionnisteController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', UserRole::RECEPTIONIST)->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('actif', $request->status === 'active' ? 1 : 0);
        }

        $stats = [
            'total' => User::where('role', UserRole::RECEPTIONIST)->count(),
            'actifs' => User::where('role', UserRole::RECEPTIONIST)->where('actif', true)->count(),
            'inactifs' => User::where('role', UserRole::RECEPTIONIST)->where('actif', false)->count(),
        ];

        $perPage = min((int) $request->get('per_page', 20), 9999);
        $admins = $query->paginate($perPage);

        return response()->json([
            'pagination' => $admins,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'actif' => 'boolean',
            'code_phone' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'sexe' => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'profil' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profil')) {
            $validated['profil'] = $request->file('profil')->store('profiles', 'public');
        }

        $plainPassword = \Illuminate\Support\Str::random(12);
        $validated['password'] = Hash::make($plainPassword);
        $validated['role'] = UserRole::RECEPTIONIST;
        $validated['actif'] = $request->has('actif') ? filter_var($request->actif, FILTER_VALIDATE_BOOLEAN) : true;
        $validated['is_verified'] = true;

        $admin = User::create($validated);

        \Illuminate\Support\Facades\Mail::to($admin->email)->send(new \App\Mail\AccountCreatedMail($admin, $plainPassword));

        return response()->json(['data' => $admin, 'message' => 'Receptionniste créé avec succès. Le mot de passe généré est : ' . $plainPassword], 201);
    }

    public function show(string $id)
    {
        $admin = User::where('role', UserRole::RECEPTIONIST)->findOrFail($id);
        return response()->json(['data' => $admin]);
    }

    public function update(Request $request, string $id)
    {
        $admin = User::where('role', UserRole::RECEPTIONIST)->findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6',
            'actif' => 'boolean',
            'code_phone' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'sexe' => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'profil' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profil')) {
            if ($admin->profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($admin->profil);
            }
            $validated['profil'] = $request->file('profil')->store('profiles', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['actif'])) {
            $validated['actif'] = filter_var($validated['actif'], FILTER_VALIDATE_BOOLEAN);
        }

        $admin->update($validated);

        return response()->json(['data' => $admin, 'message' => 'Receptionniste mis à jour avec succès.']);
    }

    public function destroy(Request $request, string $id)
    {
        $admin = User::where('role', UserRole::RECEPTIONIST)->findOrFail($id);

        $request->validate([
            'password_confirmation' => 'required|string'
        ]);

        if (!Hash::check($request->password_confirmation, Auth::user()->password)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 403);
        }

        if (Auth::id() === $admin->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $admin->delete();

        return response()->json(['message' => 'Receptionniste supprimé avec succès.']);
    }

    public function forceVerify(string $id)
    {
        $admin = User::where('role', UserRole::RECEPTIONIST)->findOrFail($id);
        $admin->is_verified = true;
        $admin->last_login = now();
        $admin->save();

        return response()->json(['message' => 'Compte vérifié avec succès.', 'data' => $admin]);
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,delete',
            'password_confirmation' => 'required_if:action,delete|string'
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];

        if ($action === 'delete') {
            if (!Hash::check($request->password_confirmation, Auth::user()->password)) {
                return response()->json(['message' => 'Mot de passe incorrect.'], 403);
            }

            $ids = array_diff($ids, [Auth::id()]);
            if (empty($ids)) {
                return response()->json(['message' => 'Aucun compte valide à supprimer.'], 400);
            }
            
            User::whereIn('id', $ids)->where('role', UserRole::RECEPTIONIST)->delete();
            return response()->json(['message' => count($ids) . ' Receptionniste(s) supprimé(s).']);
        }

        if ($action === 'activate') {
            User::whereIn('id', $ids)->where('role', UserRole::RECEPTIONIST)->update(['actif' => true]);
            return response()->json(['message' => count($ids) . ' Receptionniste(s) activé(s).']);
        }

        if ($action === 'deactivate') {
            $ids = array_diff($ids, [Auth::id()]);
            if (empty($ids)) {
                return response()->json(['message' => 'Aucun compte valide à désactiver.'], 400);
            }
            User::whereIn('id', $ids)->where('role', UserRole::RECEPTIONIST)->update(['actif' => false]);
            return response()->json(['message' => count($ids) . ' Receptionniste(s) désactivé(s).']);
        }
    }

    public function terminateSessions(Request $request, string $id)
    {
        $admin = User::where('role', UserRole::RECEPTIONIST)->findOrFail($id);
        $admin->terminateSessions();
        return response()->json(['message' => 'Toutes les sessions de cet Receptionniste ont été déconnectées avec succès.']);
    }

    public function toggleActif(Request $request, string $id)
    {
        $admin = User::where('role', UserRole::RECEPTIONIST)->findOrFail($id);
        $request->validate([
            'actif' => 'required|boolean'
        ]);

        if (Auth::id() === $admin->id && !$request->actif) {
            return response()->json(['message' => 'Vous ne pouvez pas désactiver votre propre compte.'], 403);
        }

        $admin->actif = $request->actif;
        $admin->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'data' => $admin
        ]);
    }
}


