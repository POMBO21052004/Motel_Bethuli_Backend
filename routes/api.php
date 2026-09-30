<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - Motel Bethuli
|--------------------------------------------------------------------------
*/

// --- Public Auth Routes ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/forgot-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'resetPassword']);
// --- Public Rooms Routes ---
Route::get('/rooms', [\App\Http\Controllers\Api\Public\RoomController::class, 'index']);
Route::get('/rooms/{id}', [\App\Http\Controllers\Api\Public\RoomController::class, 'show']);

// --- Protected Routes ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- Admin Routes ---
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
        Route::get('/users/{role}', [\App\Http\Controllers\Api\Admin\AdminController::class, 'users']);
        Route::patch('/users/{id}/status', [\App\Http\Controllers\Api\Admin\AdminController::class, 'updateUserStatus']);
        Route::get('/reservations', [\App\Http\Controllers\Api\Admin\AdminController::class, 'reservations']);
        Route::post('/reservations', [\App\Http\Controllers\Api\Admin\ReservationController::class, 'store']);
        Route::get('/reservations/check-availability', [\App\Http\Controllers\Api\Admin\ReservationController::class, 'checkAvailability']);
        Route::get('/reservations/{id}', [\App\Http\Controllers\Api\Admin\ReservationController::class, 'show']);
        Route::put('/reservations/{id}', [\App\Http\Controllers\Api\Admin\ReservationController::class, 'update']);
        Route::delete('/reservations/{id}', [\App\Http\Controllers\Api\Admin\ReservationController::class, 'destroy']);
        Route::patch('/reservations/{id}/status', [\App\Http\Controllers\Api\Admin\AdminController::class, 'updateReservationStatus']);
        Route::get('/ratings', [\App\Http\Controllers\Api\Admin\RatingController::class, 'index']);
        Route::delete('/ratings/{id}', [\App\Http\Controllers\Api\Admin\RatingController::class, 'destroy']);

        // Administrateurs Management
        Route::get('/administrateurs', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'index']);
        Route::post('/administrateurs', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'store']);
        Route::post('/administrateurs/bulk-action', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'bulkAction']);
        Route::get('/administrateurs/{id}', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'show']);
        Route::post('/administrateurs/{id}', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'update']);
        Route::delete('/administrateurs/{id}', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'destroy']);
        Route::patch('/administrateurs/{id}', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'toggleActif']);
        Route::post('/administrateurs/{id}/force-verify', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'forceVerify']);
        Route::post('/administrateurs/{id}/terminate-sessions', [\App\Http\Controllers\Api\Admin\AdministrateurController::class, 'terminateSessions']);

        // Receptionnistes Management
        Route::get('/receptionnistes', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'index']);
        Route::post('/receptionnistes', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'store']);
        Route::post('/receptionnistes/bulk-action', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'bulkAction']);
        Route::get('/receptionnistes/{id}', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'show']);
        Route::post('/receptionnistes/{id}', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'update']);
        Route::delete('/receptionnistes/{id}', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'destroy']);
        Route::patch('/receptionnistes/{id}', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'toggleActif']);
        Route::post('/receptionnistes/{id}/force-verify', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'forceVerify']);
        Route::post('/receptionnistes/{id}/terminate-sessions', [\App\Http\Controllers\Api\Admin\ReceptionnisteController::class, 'terminateSessions']);

        // Clients Management
        Route::get('/clients', [\App\Http\Controllers\Api\Admin\ClientController::class, 'index']);
        Route::post('/clients', [\App\Http\Controllers\Api\Admin\ClientController::class, 'store']);
        Route::post('/clients/bulk-action', [\App\Http\Controllers\Api\Admin\ClientController::class, 'bulkAction']);
        Route::get('/clients/{id}', [\App\Http\Controllers\Api\Admin\ClientController::class, 'show']);
        Route::post('/clients/{id}', [\App\Http\Controllers\Api\Admin\ClientController::class, 'update']);
        Route::delete('/clients/{id}', [\App\Http\Controllers\Api\Admin\ClientController::class, 'destroy']);
        Route::patch('/clients/{id}', [\App\Http\Controllers\Api\Admin\ClientController::class, 'toggleActif']);
        Route::post('/clients/{id}/force-verify', [\App\Http\Controllers\Api\Admin\ClientController::class, 'forceVerify']);
        Route::post('/clients/{id}/terminate-sessions', [\App\Http\Controllers\Api\Admin\ClientController::class, 'terminateSessions']);
        Route::post('/clients/{id}/toggle-cni-verified', [\App\Http\Controllers\Api\Admin\ClientController::class, 'toggleCniVerified']);

        // Rooms
        Route::get('/rooms', [\App\Http\Controllers\Api\Admin\RoomController::class, 'index']);
        Route::post('/rooms', [\App\Http\Controllers\Api\Admin\RoomController::class, 'store']);
        Route::get('/rooms/{id}', [\App\Http\Controllers\Api\Admin\RoomController::class, 'show']);
        Route::post('/rooms/{id}', [\App\Http\Controllers\Api\Admin\RoomController::class, 'update']);
        Route::delete('/rooms/{id}', [\App\Http\Controllers\Api\Admin\RoomController::class, 'destroy']);
        Route::patch('/rooms/{id}/status', [\App\Http\Controllers\Api\Admin\RoomController::class, 'updateStatus']);
        Route::patch('/rooms/{roomId}/images/{imageId}/primary', [\App\Http\Controllers\Api\Admin\RoomController::class, 'setPrimaryImage']);
    });

    Route::prefix('reception')->middleware(['role:receptionniste,admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Reception\DashboardController::class, 'index']);
        Route::get('/rooms', [\App\Http\Controllers\Api\Reception\RoomController::class, 'index']);
        Route::get('/clients', [\App\Http\Controllers\Api\Reception\ClientController::class, 'index']);
        Route::post('/clients', [\App\Http\Controllers\Api\Reception\ClientController::class, 'store']);
        Route::get('/reservations', [\App\Http\Controllers\Api\Reception\ReservationController::class, 'index']);
        Route::post('/reservations', [\App\Http\Controllers\Api\Reception\ReservationController::class, 'store']);
        Route::patch('/reservations/{id}/status', [\App\Http\Controllers\Api\Reception\ReservationController::class, 'updateStatus']);
    });

    Route::prefix('client')->middleware(['role:client,admin,receptionniste'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Client\DashboardController::class, 'index']);
        Route::get('/rooms', [\App\Http\Controllers\Api\Client\RoomController::class, 'index']);
        Route::get('/reservations', [\App\Http\Controllers\Api\Client\ReservationController::class, 'index']);
        Route::post('/reservations', [\App\Http\Controllers\Api\Client\ReservationController::class, 'store']);
        Route::get('/ratings', [\App\Http\Controllers\Api\Client\RatingController::class, 'index']);
        Route::post('/ratings', [\App\Http\Controllers\Api\Client\RatingController::class, 'store']);
        Route::get('/profile', [\App\Http\Controllers\Api\Client\ProfileController::class, 'show']);
        Route::post('/profile', [\App\Http\Controllers\Api\Client\ProfileController::class, 'update']);
        Route::post('/profile/password', [\App\Http\Controllers\Api\Client\ProfileController::class, 'updatePassword']);
        Route::post('/profile/cni', [\App\Http\Controllers\Api\Client\ProfileController::class, 'updateCni']);
    });
});
