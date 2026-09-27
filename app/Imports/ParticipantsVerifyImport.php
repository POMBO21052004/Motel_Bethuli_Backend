<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\SessionFormation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantsVerifyImport implements ToCollection, WithHeadingRow
{
    private $sessionFormationId;
    private $entrepriseId;
    private $resultats;

    public function __construct($sessionFormationId, $entrepriseId)
    {
        $this->sessionFormationId = $sessionFormationId;
        $this->entrepriseId = $entrepriseId;
        $this->resultats = [
            'total_lignes'    => 0,
            'total_succes'    => 0,
            'nb_creations'    => 0,
            'nb_mises_a_jour' => 0,
            'total_rejets'    => 0,
            'erreurs'         => [],
            'lignes'          => [],
        ];
    }

    public function headingRow(): int
    {
        return 16;
    }

    public function collection(Collection $rows)
    {
        $session = SessionFormation::with('participants')->findOrFail($this->sessionFormationId);
        $emailsDejaDansSession = $session->participants
            ->pluck('email')
            ->map(fn($e) => strtolower(trim($e)))
            ->toArray();

        $emailsVusDansFichier = [];

        foreach ($rows as $index => $row) {
            $ligneNumero = $index + 17;

            $nom           = $this->cleanValue($row['nom'] ?? null);
            $prenom        = $this->cleanValue($row['prenoms'] ?? $row['prenom'] ?? null);
            $email         = $this->cleanValue($row['email'] ?? null);
            $fonction      = $this->cleanValue($row['fonction'] ?? null);
            $telephone     = $this->cleanValue($row['telephone'] ?? null);
            $numero_permis = $this->extractNumeroPermis($row);

            if (empty($nom) && empty($prenom) && empty($email) && empty($fonction) && empty($telephone) && empty($numero_permis)) {
                continue;
            }
            if ($nom && str_contains($nom, 'Free Engineering Consultancy')) {
                continue;
            }

            $this->resultats['total_lignes']++;

            $champsErreur = [];
            $motifs       = [];

            if (empty($nom)) {
                $champsErreur[] = 'nom';
                $motifs[]       = 'Nom manquant';
            }

            if (empty($prenom)) {
                $champsErreur[] = 'prenom';
                $motifs[]       = 'Prenom manquant';
            }

            if (empty($email)) {
                $champsErreur[] = 'email';
                $motifs[]       = 'Email manquant';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $champsErreur[] = 'email';
                $motifs[]       = 'Format email invalide';
            } else {
                $emailNormalized = strtolower(trim($email));
                if (isset($emailsVusDansFichier[$emailNormalized])) {
                    $premiereLigne  = $emailsVusDansFichier[$emailNormalized];
                    $champsErreur[] = 'email';
                    $motifs[]       = "Doublon dans le fichier (1ere occurrence ligne {$premiereLigne})";
                }
            }

            if (!empty($champsErreur)) {
                $this->resultats['total_rejets']++;
                $motif = implode(' . ', $motifs);

                $this->resultats['erreurs'][] = [
                    'ligne'  => $ligneNumero,
                    'nom'    => $nom ?? '',
                    'prenom' => $prenom ?? '',
                    'email'  => $email ?? '',
                    'motif'  => $motif,
                ];

                $this->resultats['lignes'][] = [
                    'ligne'         => $ligneNumero,
                    'statut'        => 'rejet',
                    'action'        => null,
                    'nom'           => $nom ?? '',
                    'prenom'        => $prenom ?? '',
                    'email'         => $email ?? '',
                    'fonction'      => $fonction ?? '',
                    'telephone'     => $telephone ?? '',
                    'numero_permis' => $numero_permis ?? '',
                    'motif'         => $motif,
                    'champs_erreur' => $champsErreur,
                ];
                continue;
            }

            $emailNormalized = strtolower(trim($email));
            $emailsVusDansFichier[$emailNormalized] = $ligneNumero;

            try {
                $existingParticipant = Participant::where('email', $emailNormalized)->first();
                $typeAction = 'creation';

                if ($existingParticipant) {
                    $updateData = [
                        'nom'           => $nom,
                        'prenom'        => $prenom,
                        'entreprise_id' => $this->entrepriseId,
                    ];
                    if (!empty($fonction))  $updateData['fonction']  = $fonction;
                    if (!empty($telephone)) $updateData['telephone'] = $telephone;

                    $existingParticipant->fill($updateData);
                    $isDirty     = $existingParticipant->isDirty();
                    $dejaInscrit = in_array($emailNormalized, $emailsDejaDansSession);

                    if ($isDirty || !$dejaInscrit || !empty($numero_permis)) {
                        $this->resultats['nb_mises_a_jour']++;
                        if (!$dejaInscrit) {
                            $emailsDejaDansSession[] = $emailNormalized;
                        }
                    }
                    $typeAction = 'mise_a_jour';
                    $this->resultats['total_succes']++;
                } else {
                    $this->resultats['nb_creations']++;
                    $this->resultats['total_succes']++;
                }

                $this->resultats['lignes'][] = [
                    'ligne'         => $ligneNumero,
                    'statut'        => 'valide',
                    'action'        => $typeAction,
                    'nom'           => $nom ?? '',
                    'prenom'        => $prenom ?? '',
                    'email'         => $email ?? '',
                    'fonction'      => $fonction ?? '',
                    'telephone'     => $telephone ?? '',
                    'numero_permis' => $numero_permis ?? '',
                    'motif'         => null,
                    'champs_erreur' => [],
                ];

            } catch (\Exception $e) {
                $this->resultats['total_rejets']++;
                $motif = 'Erreur systeme: ' . $e->getMessage();

                $this->resultats['erreurs'][] = [
                    'ligne'  => $ligneNumero,
                    'nom'    => $nom ?? '',
                    'prenom' => $prenom ?? '',
                    'email'  => $email ?? '',
                    'motif'  => $motif,
                ];

                $this->resultats['lignes'][] = [
                    'ligne'         => $ligneNumero,
                    'statut'        => 'rejet',
                    'action'        => null,
                    'nom'           => $nom ?? '',
                    'prenom'        => $prenom ?? '',
                    'email'         => $email ?? '',
                    'fonction'      => $fonction ?? '',
                    'telephone'     => $telephone ?? '',
                    'numero_permis' => $numero_permis ?? '',
                    'motif'         => $motif,
                    'champs_erreur' => ['systeme'],
                ];
            }
        }
    }

    private function cleanValue($value): ?string
    {
        if ($value === null) return null;
        $cleaned = trim((string) $value);
        return $cleaned === '' ? null : $cleaned;
    }

    private function extractNumeroPermis($row): ?string
    {
        return $this->cleanValue(
            $row['numero_permis']
            ?? $row['numero permis']
            ?? $row['numero-permis']
            ?? null
        );
    }

    public function getResultats(): array
    {
        return $this->resultats;
    }
}
