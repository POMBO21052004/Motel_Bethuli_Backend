<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attestation;
use App\Models\Entreprise;
use App\Models\Formation;
use App\Models\Participant;
use App\Models\SessionFormation;
use App\Models\TypeAttestation;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Recherche globale multi-entités
     * GET /api/search?q=terme&limit=5
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        $limit = min((int) $request->get('limit', 5), 10);

        if (strlen($query) < 2) {
            return response()->json(['results' => [], 'query' => $query, 'total' => 0]);
        }

        $term = '%' . $query . '%';
        $results = [];
        $total = 0;

        // ── Apprenants (Participants) ──
        $participants = Participant::where('nom', 'LIKE', $term)
            ->orWhere('prenom', 'LIKE', $term)
            ->orWhere('email', 'LIKE', $term)
            ->orWhere('telephone', 'LIKE', $term)
            ->limit($limit)
            ->get(['id', 'nom', 'prenom', 'email', 'fonction']);

        if ($participants->count()) {
            $results[] = [
                'category' => 'Apprenants',
                'icon' => 'users',
                'path_prefix' => '/apprenants',
                'items' => $participants->map(fn($p) => [
                    'id' => $p->id,
                    'label' => "{$p->prenom} {$p->nom}",
                    'sublabel' => $p->email ?? $p->fonction,
                    'path' => "/apprenants/{$p->id}",
                ]),
            ];
            $total += $participants->count();
        }

        // ── Entreprises ──
        $entreprises = Entreprise::where('nom', 'LIKE', $term)
            ->orWhere('email', 'LIKE', $term)
            ->orWhere('telephone', 'LIKE', $term)
            ->orWhere('adresse', 'LIKE', $term)
            ->limit($limit)
            ->get(['id', 'nom', 'email', 'adresse']);

        if ($entreprises->count()) {
            $results[] = [
                'category' => 'Entreprises',
                'icon' => 'building2',
                'path_prefix' => '/entreprises',
                'items' => $entreprises->map(fn($e) => [
                    'id' => $e->id,
                    'label' => $e->nom,
                    'sublabel' => $e->adresse ?? $e->email,
                    'path' => "/entreprises/{$e->id}",
                ]),
            ];
            $total += $entreprises->count();
        }

        // ── Formations (Catalogue) ──
        $formations = Formation::where('intitule', 'LIKE', $term)
            ->orWhere('domaine', 'LIKE', $term)
            ->limit($limit)
            ->get(['id', 'intitule', 'domaine', 'statut']);

        if ($formations->count()) {
            $results[] = [
                'category' => 'Formations',
                'icon' => 'book-open',
                'path_prefix' => '/catalogue',
                'items' => $formations->map(fn($f) => [
                    'id' => $f->id,
                    'label' => $f->intitule,
                    'sublabel' => $f->domaine,
                    'badge' => $f->statut,
                    'path' => "/catalogue/{$f->id}",
                ]),
            ];
            $total += $formations->count();
        }

        // ── Sessions ──
        $sessions = SessionFormation::where('nom', 'LIKE', $term)
            ->orWhere('lieu', 'LIKE', $term)
            ->limit($limit)
            ->get(['id', 'nom', 'lieu', 'date_debut', 'statut']);

        if ($sessions->count()) {
            $results[] = [
                'category' => 'Sessions',
                'icon' => 'calendar-days',
                'path_prefix' => '/sessions',
                'items' => $sessions->map(fn($s) => [
                    'id' => $s->id,
                    'label' => $s->nom,
                    'sublabel' => $s->lieu ?? ($s->date_debut ? date('d/m/Y', strtotime($s->date_debut)) : null),
                    'badge' => $s->statut,
                    'path' => "/sessions/{$s->id}",
                ]),
            ];
            $total += $sessions->count();
        }

        // ── Attestations ──
        $attestations = Attestation::with(['participant'])
            ->where('numero', 'LIKE', $term)
            ->limit($limit)
            ->get();

        if ($attestations->count()) {
            $results[] = [
                'category' => 'Attestations',
                'icon' => 'award',
                'path_prefix' => '/attestations',
                'items' => $attestations->map(fn($a) => [
                    'id' => $a->id,
                    'label' => $a->numero,
                    'sublabel' => $a->participant ? "{$a->participant->prenom} {$a->participant->nom}" : null,
                    'path' => "/attestations",
                    'action' => 'download_pdf',
                    'session_id' => $a->session_id,
                    'participant_id' => $a->participant_id,
                ]),
            ];
            $total += $attestations->count();
        }

        // ── Uniquement pour les Administrateurs ──
        if ($request->user() && $request->user()->role === 'admin') {
            // ── Modèles d'Attestation (TypeAttestation) ──
            $typeAttestations = TypeAttestation::where('name', 'LIKE', $term)
                ->orWhere('model_choice', 'LIKE', $term)
                ->limit($limit)
                ->get(['id', 'name', 'model_choice', 'duration_value', 'duration_unit']);

            if ($typeAttestations->count()) {
                $results[] = [
                    'category' => "Modèles d'Attestation",
                    'icon' => 'award',
                    'path_prefix' => '/type-attestations',
                    'items' => $typeAttestations->map(fn($t) => [
                        'id' => $t->id,
                        'label' => $t->name,
                        'sublabel' => $t->model_choice ? "Modèle : {$t->model_choice}" : null,
                        'path' => "/type-attestations/{$t->id}/edit",
                    ]),
                ];
                $total += $typeAttestations->count();
            }

            // ── Administrateurs & Gestionnaires ──
            $users = User::where(function ($q) use ($term) {
                    $q->where('role', 'admin')->orWhere('role', 'gestionnaire');
                })
                ->where(function ($q) use ($term) {
                    $q->where('nom', 'LIKE', $term)
                      ->orWhere('prenom', 'LIKE', $term)
                      ->orWhere('email', 'LIKE', $term);
                })
                ->limit($limit)
                ->get(['id', 'nom', 'prenom', 'email', 'role']);

            if ($users->count()) {
                $admins = $users->where('role', 'admin');
                $gestionnaires = $users->where('role', 'gestionnaire');

                if ($admins->count()) {
                    $results[] = [
                        'category' => 'Administrateurs',
                        'icon' => 'shield-check',
                        'path_prefix' => '/administrateurs',
                        'items' => $admins->map(fn($u) => [
                            'id' => $u->id,
                            'label' => "{$u->prenom} {$u->nom}",
                            'sublabel' => $u->email,
                            'path' => "/administrateurs/{$u->id}",
                        ])->values(),
                    ];
                    $total += $admins->count();
                }

                if ($gestionnaires->count()) {
                    $results[] = [
                        'category' => 'Gestionnaires',
                        'icon' => 'user-cog',
                        'path_prefix' => '/gestionnaires',
                        'items' => $gestionnaires->map(fn($u) => [
                            'id' => $u->id,
                            'label' => "{$u->prenom} {$u->nom}",
                            'sublabel' => $u->email,
                            'path' => "/gestionnaires/{$u->id}",
                        ])->values(),
                    ];
                    $total += $gestionnaires->count();
                }
            }
        }

        return response()->json([
            'results' => $results,
            'query' => $query,
            'total' => $total,
        ]);
    }
}
