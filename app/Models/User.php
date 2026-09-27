<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'nom',
        'prenom',
        'actif',
        'role',
        'last_login',
        'is_verified',
        'profil',
        'code_phone',
        'phone',
        'sexe',
        'date_naissance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'actif'             => 'boolean',
            'is_verified'       => 'boolean',
            'last_login'        => 'datetime',
            'role'              => UserRole::class,   // Cast en Enum natif
        ];
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────────

    protected $appends = ['is_online'];

    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }

    public function isOnline(): bool
    {
        return $this->tokens()
            ->where(function ($query) {
                $query->where('last_used_at', '>', now()->subMinutes(120))
                      ->orWhere('created_at', '>', now()->subMinutes(120));
            })
            ->exists();
    }

    // ─── Helpers rôle ────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isReceptionist(): bool
    {
        return $this->role === UserRole::RECEPTIONIST;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    /**
     * Profil détaillé (uniquement pour les clients).
     */
    public function customerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    /**
     * Réservations effectuées par le client.
     */
    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    /**
     * Réservations saisies par la réceptionniste.
     */
    public function processedReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reservation::class, 'receptionist_id');
    }

    /**
     * Notations laissées par le client.
     */
    public function ratings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReservationRating::class, 'client_id');
    }

    /**
     * Sessions actives — session termination helper.
     */
    public function terminateSessions(): int
    {
        return $this->tokens()->delete();
    }
}
