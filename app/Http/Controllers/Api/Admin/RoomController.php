<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use App\Enums\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Liste toutes les chambres avec filtres et stats.
     */
    public function index(Request $request)
    {
        $query = Room::with(['primaryImage', 'images'])->orderBy('floor')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description_fr', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->where('floor', $request->floor);
        }

        $stats = [
            'total'       => Room::count(),
            'available'   => Room::where('status', RoomStatus::AVAILABLE)->count(),
            'occupied'    => Room::where('status', RoomStatus::OCCUPIED)->count(),
            'maintenance' => Room::where('status', RoomStatus::MAINTENANCE)->count(),
            'floors'      => Room::distinct()->orderBy('floor')->pluck('floor'),
        ];

        $perPage = min((int) $request->get('per_page', 12), 100);
        $rooms = $query->paginate($perPage);

        return response()->json([
            'pagination' => $rooms,
            'stats'      => $stats,
        ]);
    }

    /**
     * Créer une nouvelle chambre.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100|unique:rooms,name',
            'floor'          => 'required|integer|min:0|max:50',
            'description_fr' => 'required|string|max:2000',
            'description_en' => 'nullable|string|max:2000',
            'capacity'       => 'required|integer|min:1|max:20',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day'  => 'required|numeric|min:0',
            'status'         => ['required', Rule::in(RoomStatus::values())],
            'images'         => 'nullable|array|max:10',
            'images.*'       => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_urls'     => 'nullable|array|max:10',
            'image_urls.*'   => 'url|max:2000',
            'primary_image'  => 'nullable|integer|min:0',
        ]);

        $room = Room::create([
            'name'           => $validated['name'],
            'floor'          => $validated['floor'],
            'description_fr' => $validated['description_fr'],
            'description_en' => $validated['description_en'] ?? null,
            'capacity'       => $validated['capacity'],
            'price_per_hour' => $validated['price_per_hour'] ?? null,
            'price_per_day'  => $validated['price_per_day'],
            'status'         => $validated['status'],
        ]);

        $primaryIndex = $validated['primary_image'] ?? 0;
        $currentIndex = 0;

        // Upload des images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('rooms', 'public');
                RoomImage::create([
                    'room_id'    => $room->id,
                    'image_path' => $path,
                    'is_primary' => ($currentIndex === (int)$primaryIndex),
                ]);
                $currentIndex++;
            }
        }

        // Sauvegarde des URLs d'images
        if ($request->has('image_urls')) {
            foreach ($request->input('image_urls') as $url) {
                RoomImage::create([
                    'room_id'    => $room->id,
                    'image_path' => $url,
                    'is_primary' => ($currentIndex === (int)$primaryIndex),
                ]);
                $currentIndex++;
            }
        }

        return response()->json([
            'data'    => $room->load('images'),
            'message' => 'Chambre créée avec succès.',
        ], 201);
    }

    /**
     * Afficher une chambre.
     */
    public function show(string $id)
    {
        $room = Room::with(['images', 'reservations.client'])->findOrFail($id);
        return response()->json(['data' => $room]);
    }

    /**
     * Mettre à jour une chambre.
     */
    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name'           => ['sometimes', 'required', 'string', 'max:100', Rule::unique('rooms', 'name')->ignore($id)],
            'floor'          => 'sometimes|required|integer|min:0|max:50',
            'description_fr' => 'sometimes|required|string|max:2000',
            'description_en' => 'nullable|string|max:2000',
            'capacity'       => 'sometimes|required|integer|min:1|max:20',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day'  => 'sometimes|required|numeric|min:0',
            'status'         => ['sometimes', 'required', Rule::in(RoomStatus::values())],
            'images'         => 'nullable|array|max:10',
            'images.*'       => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_urls'     => 'nullable|array|max:10',
            'image_urls.*'   => 'url|max:2000',
            'primary_image'  => 'nullable|integer|min:0',
            'existing_primary_id' => 'nullable|string|exists:room_images,id',
            'delete_image_ids' => 'nullable|array',
            'delete_image_ids.*' => 'exists:room_images,id',
        ]);

        $room->update([
            'name'           => $validated['name'] ?? $room->name,
            'floor'          => $validated['floor'] ?? $room->floor,
            'description_fr' => $validated['description_fr'] ?? $room->description_fr,
            'description_en' => $validated['description_en'] ?? $room->description_en,
            'capacity'       => $validated['capacity'] ?? $room->capacity,
            'price_per_hour' => $validated['price_per_hour'] ?? $room->price_per_hour,
            'price_per_day'  => $validated['price_per_day'] ?? $room->price_per_day,
            'status'         => $validated['status'] ?? $room->status,
        ]);

        // Suppression d'images sélectionnées
        if (!empty($validated['delete_image_ids'])) {
            $toDelete = RoomImage::whereIn('id', $validated['delete_image_ids'])->where('room_id', $id)->get();
            foreach ($toDelete as $img) {
                if (!str_starts_with($img->image_path, 'http')) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
        }

        // Définir une image existante comme principale
        if (!empty($validated['existing_primary_id'])) {
            $img = RoomImage::where('id', $validated['existing_primary_id'])->where('room_id', $id)->first();
            if ($img) {
                RoomImage::where('room_id', $id)->update(['is_primary' => false]);
                $img->update(['is_primary' => true]);
            }
        }

        $existingCount = $room->images()->count();
        $primaryIndex  = $validated['primary_image'] ?? null;
        $currentIndex = 0;

        // Ajout de nouvelles images (fichiers)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('rooms', 'public');
                $isPrimary = ($primaryIndex !== null && $currentIndex === (int)$primaryIndex)
                    || ($existingCount === 0 && $currentIndex === 0 && $primaryIndex === null);
                
                if ($isPrimary) {
                    RoomImage::where('room_id', $id)->update(['is_primary' => false]);
                }
                
                RoomImage::create([
                    'room_id'    => $room->id,
                    'image_path' => $path,
                    'is_primary' => $isPrimary,
                ]);
                $currentIndex++;
            }
        }

        // Ajout de nouvelles images (URLs)
        if ($request->has('image_urls')) {
            foreach ($request->input('image_urls') as $url) {
                $isPrimary = ($primaryIndex !== null && $currentIndex === (int)$primaryIndex)
                    || ($existingCount === 0 && $currentIndex === 0 && $primaryIndex === null);
                
                if ($isPrimary) {
                    RoomImage::where('room_id', $id)->update(['is_primary' => false]);
                }
                
                RoomImage::create([
                    'room_id'    => $room->id,
                    'image_path' => $url,
                    'is_primary' => $isPrimary,
                ]);
                $currentIndex++;
            }
        }

        return response()->json([
            'data'    => $room->fresh()->load('images'),
            'message' => 'Chambre mise à jour avec succès.',
        ]);
    }

    /**
     * Supprimer une chambre.
     */
    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);

        // Supprimer toutes les images du stockage
        foreach ($room->images as $image) {
            if (!str_starts_with($image->image_path, 'http')) {
                Storage::disk('public')->delete($image->image_path);
            }
        }
        $room->images()->delete();
        $room->delete();

        return response()->json(['message' => 'Chambre supprimée avec succès.']);
    }

    /**
     * Changer le statut d'une chambre rapidement.
     */
    public function updateStatus(Request $request, string $id)
    {
        $room = Room::findOrFail($id);
        $request->validate([
            'status' => ['required', Rule::in(RoomStatus::values())],
        ]);
        $room->update(['status' => $request->status]);
        return response()->json([
            'data'    => $room->fresh(),
            'message' => 'Statut mis à jour.',
        ]);
    }

    /**
     * Définir une image comme image principale.
     */
    public function setPrimaryImage(Request $request, string $roomId, string $imageId)
    {
        $room = Room::findOrFail($roomId);
        RoomImage::where('room_id', $roomId)->update(['is_primary' => false]);
        $image = RoomImage::where('room_id', $roomId)->findOrFail($imageId);
        $image->update(['is_primary' => true]);
        return response()->json(['message' => 'Image principale mise à jour.']);
    }
}
