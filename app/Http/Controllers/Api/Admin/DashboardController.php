<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationRating;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today          = Carbon::today();
        $thisMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        // ── Chambres ────────────────────────────────────────────────────────────
        $totalRooms       = Room::count();
        $availableRooms   = Room::where('status', RoomStatus::AVAILABLE)->count();
        $maintenanceRooms = Room::where('status', RoomStatus::MAINTENANCE)->count();

        // Taux d'occupation : basé sur les réservations confirmées en cours actuellement
        $occupiedRooms = Reservation::where('status', ReservationStatus::CONFIRMED)
            ->where('reservation_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereDate('reservation_date', Carbon::today())
                       ->where('start_time', '<=', Carbon::now()->format('H:i'));
                })->orWhere('reservation_date', '<', Carbon::today());
            })
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereDate('end_date', Carbon::today())
                       ->where('end_time', '>=', Carbon::now()->format('H:i'));
                })->orWhere('end_date', '>', Carbon::today());
            })
            ->distinct('room_id')
            ->count('room_id');

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // ── Réservations ─────────────────────────────────────────────────────────
        $totalReservations     = Reservation::count();
        $todayReservations     = Reservation::where('reservation_date', '<=', $today)->where('end_date', '>=', $today)->count();
        $pendingReservations   = Reservation::where('status', ReservationStatus::PENDING)->count();
        $confirmedReservations = Reservation::where('status', ReservationStatus::CONFIRMED)->count();
        $cancelledReservations = Reservation::where('status', ReservationStatus::CANCELLED)->count();

        // Réservations en attente depuis plus de 48h
        $expiredPending = Reservation::where('status', ReservationStatus::PENDING)
            ->where('created_at', '<', Carbon::now()->subHours(48))
            ->count();

        // Réservations ce mois-ci vs mois dernier
        $thisMonthReservations = Reservation::where('created_at', '>=', $thisMonthStart)->count();
        $lastMonthReservations = Reservation::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $reservationsGrowth    = $lastMonthReservations > 0
            ? round((($thisMonthReservations - $lastMonthReservations) / $lastMonthReservations) * 100, 1)
            : ($thisMonthReservations > 0 ? 100 : 0);

        // ── Revenus (uniquement les réservations confirmées) ──────────────────────
        $thisMonthRevenue = Reservation::where('status', ReservationStatus::CONFIRMED->value)
            ->where('created_at', '>=', $thisMonthStart)
            ->sum('total_price');

        $lastMonthRevenue = Reservation::where('status', ReservationStatus::CONFIRMED->value)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_price');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100 : 0);

        $totalRevenue = Reservation::where('status', ReservationStatus::CONFIRMED->value)->sum('total_price');

        // ── Utilisateurs ──────────────────────────────────────────────────────────
        $totalClients    = User::where('role', UserRole::CLIENT)->count();
        $activeClients   = User::where('role', UserRole::CLIENT)->where('actif', true)->count();
        $totalReception  = User::where('role', UserRole::RECEPTIONIST)->count();
        $newClientsMonth = User::where('role', UserRole::CLIENT)
            ->where('created_at', '>=', $thisMonthStart)->count();

        // ── Avis ──────────────────────────────────────────────────────────────────
        $averageRating   = round((float) ReservationRating::avg('rating'), 1);
        $totalRatings    = ReservationRating::count();
        $ratingBreakdown = ReservationRating::select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderByDesc('rating')
            ->pluck('count', 'rating');

        // ── Réservations récentes ─────────────────────────────────────────────────
        $recentReservations = Reservation::with([
            'room:id,name,floor',
            'room.primaryImage:id,room_id,image_path',
            'client:id,nom,prenom,email,phone,profil',
        ])
        ->select('id', 'room_id', 'client_id', 'reservation_date', 'end_date', 'start_time', 'end_time', 'status')
        ->latest('created_at')
        ->limit(8)
        ->get();

        // ── Activité des 7 derniers jours ─────────────────────────────────────────
        $weeklyActivity = Reservation::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw("sum(CASE WHEN status = 'confirmed' THEN total_price ELSE 0 END) as revenue")
            )
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Remplir les jours manquants avec 0
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date   = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[] = [
                'date'    => $date,
                'label'   => Carbon::now()->subDays($i)->locale('fr')->isoFormat('ddd D'),
                'total'   => $weeklyActivity[$date]->total ?? 0,
                'revenue' => (float) ($weeklyActivity[$date]->revenue ?? 0),
            ];
        }

        // ── Top chambres (les plus réservées) ────────────────────────────────────
        $topRooms = Room::select('rooms.id', 'rooms.name', 'rooms.price_per_day', 'rooms.status')
            ->withCount(['reservations as reservations_count' => function ($query) {
                $query->whereNotIn('status', [ReservationStatus::CANCELLED->value]);
            }])
            ->orderByDesc('reservations_count')
            ->limit(5)
            ->get();

        return response()->json([
            'rooms' => [
                'total'          => $totalRooms,
                'available'      => $availableRooms,
                'occupied_now'   => $occupiedRooms,
                'maintenance'    => $maintenanceRooms,
                'occupancy_rate' => $occupancyRate,
            ],
            'reservations' => [
                'total'           => $totalReservations,
                'today'           => $todayReservations,
                'pending'         => $pendingReservations,
                'confirmed'       => $confirmedReservations,
                'cancelled'       => $cancelledReservations,
                'expired_pending' => $expiredPending,
                'this_month'      => $thisMonthReservations,
                'growth'          => $reservationsGrowth,
            ],
            'revenue' => [
                'total'      => (float) $totalRevenue,
                'this_month' => (float) $thisMonthRevenue,
                'last_month' => (float) $lastMonthRevenue,
                'growth'     => $revenueGrowth,
            ],
            'clients' => [
                'total'        => $totalClients,
                'active'       => $activeClients,
                'new_month'    => $newClientsMonth,
                'receptionists' => $totalReception,
            ],
            'ratings' => [
                'average'   => $averageRating ?: 0,
                'total'     => $totalRatings,
                'breakdown' => $ratingBreakdown,
            ],
            'recent_reservations' => $recentReservations,
            'weekly_activity'     => $days,
            'top_rooms'           => $topRooms,
        ]);
    }
}
