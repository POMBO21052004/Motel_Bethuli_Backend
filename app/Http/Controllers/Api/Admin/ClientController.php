<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', UserRole::CLIENT)->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('actif', $request->status === 'active' ? 1 : 0);
        }

        $stats = [
            'total'    => User::where('role', UserRole::CLIENT)->count(),
            'actifs'   => User::where('role', UserRole::CLIENT)->where('actif', true)->count(),
            'inactifs' => User::where('role', UserRole::CLIENT)->where('actif', false)->count(),
        ];

        $perPage = min((int) $request->get('per_page', 20), 9999);
        $clients = $query->paginate($perPage);

        return response()->json([
            'pagination' => $clients,
            'stats'      => $stats,
        ]);
    }

    public function show(string $id)
    {
        $client = User::where('role', UserRole::CLIENT)->findOrFail($id);

        // Charger les réservations avec chambre associée
        $reservations = $client->reservations()
            ->with(['room:id,name,floor,price_per_day'])
            ->orderBy('reservation_date', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'               => $r->id,
                    'room_name'        => $r->room?->name,
                    'floor'            => $r->room?->floor,
                    'reservation_date' => $r->reservation_date?->format('Y-m-d'),
                    'start_time'       => $r->start_time,
                    'end_time'         => $r->end_time,
                    'total_price'      => $r->total_price,
                    'status'           => $r->status instanceof \App\Enums\ReservationStatus
                                            ? $r->status->value
                                            : $r->status,
                    'status_label'     => $r->status instanceof \App\Enums\ReservationStatus
                                            ? $r->status->label()
                                            : $r->status,
                    'notes'            => $r->notes,
                ];
            });

        return response()->json([
            'data'         => $client,
            'reservations' => $reservations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'            => 'required|string|max:255',
            'prenom'         => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users',
            'actif'          => 'boolean',
            'code_phone'     => 'nullable|string|max:10',
            'phone'          => 'nullable|string|max:20',
            'sexe'           => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'profil'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profil')) {
            $validated['profil'] = $request->file('profil')->store('profiles', 'public');
        }

        $plainPassword = \Illuminate\Support\Str::random(12);
        $validated['password'] = Hash::make($plainPassword);
        $validated['role'] = UserRole::CLIENT;
        $validated['actif'] = $request->has('actif') ? filter_var($request->actif, FILTER_VALIDATE_BOOLEAN) : true;
        $validated['is_verified'] = true;

        $client = User::create($validated);

        return response()->json(['data' => $client, 'message' => 'Client créé avec succès. Le mot de passe généré est : ' . $plainPassword], 201);
    }

    public function update(Request $request, string $id)
    {
        $client = User::where('role', UserRole::CLIENT)->findOrFail($id);

        $validated = $request->validate([
            'nom'            => 'sometimes|required|string|max:255',
            'prenom'         => 'sometimes|required|string|max:255',
            'email'          => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password'       => 'nullable|string|min:6',
            'actif'          => 'boolean',
            'code_phone'     => 'nullable|string|max:10',
            'phone'          => 'nullable|string|max:20',
            'sexe'           => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'profil'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profil')) {
            if ($client->profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($client->profil);
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

        $client->update($validated);

        return response()->json(['data' => $client, 'message' => 'Client mis à jour avec succès.']);
    }

    public function destroy(Request $request, string $id)
    {
        $client = User::where('role', UserRole::CLIENT)->findOrFail($id);

        $request->validate(['password_confirmation' => 'required|string']);

        if (!Hash::check($request->password_confirmation, Auth::user()->password)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 403);
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès.']);
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids'                   => 'required|array',
            'ids.*'                 => 'exists:users,id',
            'action'                => 'required|in:activate,deactivate,delete',
            'password_confirmation' => 'required_if:action,delete|string',
        ]);

        $ids    = $validated['ids'];
        $action = $validated['action'];

        if ($action === 'delete') {
            if (!Hash::check($request->password_confirmation, Auth::user()->password)) {
                return response()->json(['message' => 'Mot de passe incorrect.'], 403);
            }
            User::whereIn('id', $ids)->where('role', UserRole::CLIENT)->delete();
            return response()->json(['message' => count($ids) . ' client(s) supprimé(s).']);
        }

        if ($action === 'activate') {
            User::whereIn('id', $ids)->where('role', UserRole::CLIENT)->update(['actif' => true]);
            return response()->json(['message' => count($ids) . ' client(s) activé(s).']);
        }

        if ($action === 'deactivate') {
            User::whereIn('id', $ids)->where('role', UserRole::CLIENT)->update(['actif' => false]);
            return response()->json(['message' => count($ids) . ' client(s) désactivé(s).']);
        }
    }

    public function toggleActif(Request $request, string $id)
    {
        $client = User::where('role', UserRole::CLIENT)->findOrFail($id);
        $request->validate(['actif' => 'required|boolean']);
        $client->actif = $request->actif;
        $client->save();
        return response()->json(['message' => 'Statut mis à jour.', 'data' => $client]);
    }

    public function forceVerify(string $id)
    {
        $client = User::where('role', UserRole::CLIENT)->findOrFail($id);
        $client->is_verified = true;
        $client->email_verified_at = now();
        $client->save();
        return response()->json(['message' => 'Compte vérifié.', 'data' => $client]);
    }

    public function terminateSessions(Request $request, string $id)
    {
        $client = User::where('role', UserRole::CLIENT)->findOrFail($id);
        $client->terminateSessions();
        return response()->json(['message' => 'Toutes les sessions de ce client ont été déconnectées.']);
    }
}
