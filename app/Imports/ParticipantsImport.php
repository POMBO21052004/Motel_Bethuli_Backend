<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\SessionFormation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ParticipantsImport implements ToCollection, WithHeadingRow
{
    private $sessionFormationId;
    private $entrepriseId;
    private $resultats;

    public function __construct($sessionFormationId, $entrepriseId)
    {
        $this->sessionFormationId = $sessionFormationId;
        $this->entrepriseId = $entrepriseId;
        $this->resultats = [
            'total_lignes'     => 0,
            'total_succes'     => 0,
            'nb_creations'     => 0,
            'nb_mises_a_jour'  => 0,
            'total_rejets'     => 0,
            'erreurs'          => [],
        ];
    }

    /**
     * L'en-tête est à la ligne 16 selon le modèle FEC.
     */
    public function headingRow(): int
    {
        return 16;
    }

    /**
     * Process each row of the imported Excel file.
     * Implements all 8 validation controls from FEC-REG-IMP-2025-001 Section 4.
     * Each line is treated independently (R7) — no global transaction.
     */
    public function collection(Collection $rows)
    {
        // Pre-load emails already associated with this session (for control #6)
        $session = SessionFormation::with('participants')->findOrFail($this->sessionFormationId);
        $emailsDejaDansSession = $session->participants->pluck('email')->map(fn($e) => strtolower(trim($e)))->toArray();

        // Track emails seen in the current file (for control #5 — internal dedup)
        $emailsVusDansFichier = []; // key = lowercase email, value = line number

        foreach ($rows as $index => $row) {
            $ligneNumero = $index + 17; // Data starts at line 17

            // Clean and extract data
            $nom       = $this->cleanValue($row['nom'] ?? null);
            $prenom    = $this->cleanValue($row['prenoms'] ?? $row['prenom'] ?? null);
            $email     = $this->cleanValue($row['email'] ?? null);
            $fonction  = $this->cleanValue($row['fonction'] ?? null);
            $telephone = $this->cleanValue($row['telephone'] ?? null);
            $numero_permis = $this->extractNumeroPermis($row);

            // Ignorer les lignes complètement vides (comme les 5 lignes pré-formatées du modèle)
            if (empty($nom) && empty($prenom) && empty($email) && empty($fonction) && empty($telephone) && empty($numero_permis)) {
                continue;
            }

            // Ignorer la ligne de pied de page du modèle ("© Free Engineering Consultancy...")
            if ($nom && str_contains($nom, '© Free Engineering Consultancy')) {
                continue;
            }

            $this->resultats['total_lignes']++;

            // ─── Control #1: Nom required ───
            if (empty($nom)) {
                $this->addError($ligneNumero, $nom, $prenom, $email, 'Nom manquant');
                continue;
            }

            // ─── Control #2: Prénom required ───
            if (empty($prenom)) {
                $this->addError($ligneNumero, $nom, $prenom, $email, 'Prénom manquant');
                continue;
            }

            // ─── Control #3: Email required ───
            if (empty($email)) {
                $this->addError($ligneNumero, $nom, $prenom, $email, 'Email manquant');
                continue;
            }

            // ─── Control #4: Email format validation ───
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError($ligneNumero, $nom, $prenom, $email, 'Format d\'e-mail invalide');
                continue;
            }

            $emailNormalized = strtolower(trim($email));

            // ─── Control #5: Internal file duplicate (R6) ───
            if (isset($emailsVusDansFichier[$emailNormalized])) {
                $premiereLigne = $emailsVusDansFichier[$emailNormalized];
                $this->addError($ligneNumero, $nom, $prenom, $email, "Doublon dans le fichier (1ère occurrence ligne {$premiereLigne} conservée)");
                continue;
            }
            $emailsVusDansFichier[$emailNormalized] = $ligneNumero;

            // ─── Control #6: Session duplicate (R5) ───
            $dejaInscrit = in_array($emailNormalized, $emailsDejaDansSession);

            // ─── Controls #7 & #8: Find or create participant, then attach to session ───
            try {
                $existingParticipant = Participant::where('email', $emailNormalized)->first();

                if ($existingParticipant) {
                    // Case 2 (Section 5.2): Update existing participant
                    $updateData = [
                        'nom'           => $nom,
                        'prenom'        => $prenom,
                        'entreprise_id' => $this->entrepriseId, // Always updated (R9)
                    ];

                    // Fonction & Telephone: only update if provided in file (R9)
                    if (!empty($fonction)) {
                        $updateData['fonction'] = $fonction;
                    }
                    if (!empty($telephone)) {
                        $updateData['telephone'] = $telephone;
                    }

                    $existingParticipant->fill($updateData);
                    $isDirty = $existingParticipant->isDirty();

                    if ($isDirty) {
                        $existingParticipant->save();
                    }

                    $attached = false;
                    if (!$dejaInscrit) {
                        // Attach to session with numero_permis in pivot
                        $pivotData = ['numero_permis' => $numero_permis];
                        $existingParticipant->sessions()->attach($this->sessionFormationId, $pivotData);
                        $emailsDejaDansSession[] = $emailNormalized;
                        $attached = true;
                    } elseif (!empty($numero_permis)) {
                        // Already in session — update numero_permis in pivot if provided
                        $existingParticipant->sessions()->updateExistingPivot(
                            $this->sessionFormationId,
                            ['numero_permis' => $numero_permis]
                        );
                    }

                    if ($isDirty || $attached) {
                        $this->resultats['nb_mises_a_jour']++;
                    }
                    $this->resultats['total_succes']++;

                } else {
                    // Create new participant
                    $newParticipant = Participant::create([
                        'nom'           => $nom,
                        'prenom'        => $prenom,
                        'email'         => $emailNormalized,
                        'fonction'      => $fonction,
                        'telephone'     => $telephone,
                        'entreprise_id' => $this->entrepriseId,
                    ]);

                    // Attach to session with numero_permis in pivot
                    $newParticipant->sessions()->attach($this->sessionFormationId, [
                        'numero_permis' => $numero_permis,
                    ]);

                    // Track that this email is now in the session (for subsequent lines)
                    $emailsDejaDansSession[] = $emailNormalized;

                    $this->resultats['nb_creations']++;
                    $this->resultats['total_succes']++;
                }

            } catch (\Exception $e) {
                Log::error("Import participant error line {$ligneNumero}: " . $e->getMessage());
                $this->addError($ligneNumero, $nom, $prenom, $email, 'Erreur système: ' . $e->getMessage());
            }
        }
    }

    /**
     * Clean a cell value: trim whitespace, return null if empty.
     */
    private function cleanValue($value): ?string
    {
        if ($value === null) return null;
        $cleaned = trim((string) $value);
        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Extrait le numéro de permis depuis les variantes d'en-tête Excel.
     */
    private function extractNumeroPermis($row): ?string
    {
        return $this->cleanValue(
            $row['numero_permis']
            ?? $row['numero permis']
            ?? $row['numero-permis']
            ?? null
        );
    }

    /**
     * Add an error entry to the results.
     */
    private function addError(int $ligne, ?string $nom, ?string $prenom, ?string $email, string $motif): void
    {
        $this->resultats['total_rejets']++;
        $this->resultats['erreurs'][] = [
            'ligne'  => $ligne,
            'nom'    => $nom ?? '',
            'prenom' => $prenom ?? '',
            'email'  => $email ?? '',
            'motif'  => $motif,
        ];
    }

    /**
     * Get import results for the controller to return as JSON.
     */
    public function getResultats(): array
    {
        return $this->resultats;
    }
}
