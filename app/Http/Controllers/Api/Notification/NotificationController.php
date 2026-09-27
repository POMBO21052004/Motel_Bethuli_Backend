<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Récupère les notifications de l'utilisateur connecté (paginées)
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);
        return response()->json($notifications);
    }

    /**
     * Récupère le nombre de notifications non lues
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        return response()->json(['unread_count' => $count]);
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues']);
    }

    /**
     * Supprime toutes les notifications de l'application (Admin uniquement)
     */
    public function deleteAll(Request $request)
    {
        // On vérifie que c'est bien un admin (la route devrait déjà avoir le middleware, mais double vérification)
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        \Illuminate\Support\Facades\DB::table('notifications')->truncate();

        return response()->json(['message' => 'Toutes les notifications de l\'application ont été supprimées']);
    }
}
