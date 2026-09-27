<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'cni_number',
        'cni_recto_path',
        'cni_verso_path',
        'cni_verified',
        'adresse',
        'ville',
        'pays',
        'nationalite',
    ];

    protected function casts(): array
    {
        return [
            'cni_verified' => 'boolean',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    /**
     * Utilisateur propriétaire de ce profil.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accesseurs / Helpers ────────────────────────────────────────────────

    /**
     * Vérifie si le profil CNI est complet (les deux côtés et le numéro renseignés).
     */
    public function isCniComplete(): bool
    {
        return !empty($this->cni_number)
            && !empty($this->cni_recto_path)
            && !empty($this->cni_verso_path);
    }
}
