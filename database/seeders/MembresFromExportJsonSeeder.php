<?php

namespace Database\Seeders;

use App\Models\EgliseLocale;
use App\Models\Membre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Ramsey\Uuid\Uuid;

/**
 * Données membres générées depuis l’ancien dump SQL via :
 * php database/scripts/parse_membres_sql_to_json.php [membres.sql] database/data/membres_export.json
 *
 * Les enregistrements source (eglise_id=2) sont rattachés à l’église locale MCB-TALANGAI (district Nord — Brazzaville).
 * L’identifiant_public est déterministe (UUID v5) pour permettre un re-seed idempotent.
 */
class MembresFromExportJsonSeeder extends Seeder
{
    private const EXPORT_PATH = 'data/membres_export.json';

    private const EGLISE_CODE = 'MCB-TALANGAI';

    public function run(): void
    {
        $path = database_path(self::EXPORT_PATH);
        if (! File::isReadable($path)) {
            $this->command?->warn("Fichier d’export absent : {$path} — ignorer ce seeder ou régénérer le JSON.");

            return;
        }

        $eglise = EgliseLocale::query()->where('code_unique', self::EGLISE_CODE)->first();
        if ($eglise === null) {
            $this->command?->error('Église locale '.self::EGLISE_CODE.' introuvable. Exécuter EglisesLocalesEtatDimes2025Seeder d’abord.');

            return;
        }

        $raw = json_decode(File::get($path), true);
        if (! is_array($raw)) {
            $this->command?->error('JSON membres invalide.');

            return;
        }

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($this->normStr($row['deleted_at'] ?? null) !== null) {
                continue;
            }

            if ((int) ($row['source_eglise_id'] ?? 0) !== 2) {
                continue;
            }

            $uuid = Uuid::uuid5(
                Uuid::NAMESPACE_URL,
                'https://adventiste.local/membre-import/'.$row['source_id']
            )->toString();

            Membre::query()->updateOrCreate(
                ['identifiant_public' => $uuid],
                [
                    'eglise_locale_id' => $eglise->id,
                    'groupe_mission_id' => null,
                    'nom' => (string) ($row['nom'] ?? ''),
                    'prenom' => (string) ($row['prenom'] ?? ''),
                    'sexe' => $this->normStr($row['sexe'] ?? null),
                    'date_naissance' => $this->normDate($row['date_naissance'] ?? null),
                    'lieu_naissance' => $this->normStr($row['lieu_naissance'] ?? null),
                    'noms_pere' => $this->normStr($row['nom_pere'] ?? null),
                    'noms_mere' => $this->normStr($row['nom_mere'] ?? null),
                    'adresses' => $this->buildAdresses($row),
                    'telephone' => $this->buildTelephone($row),
                    'niveau_etudes' => $this->normStr($row['formation'] ?? null),
                    'occupation' => $this->normStr($row['profession'] ?? null),
                    'situation_matrimoniale' => $this->normStr($row['situation_matrimoniale'] ?? null),
                    'date_mariage' => $this->normDate($row['date_mariage'] ?? null),
                    'conjoint' => $this->normStr($row['conjoint'] ?? null),
                    'date_bapteme' => $this->normDate($row['date_bapteme'] ?? null),
                    'lieu_bapteme' => null,
                    'religion_anterieure' => null,
                    'recu_dans_eglise_de' => null,
                    'recu_le' => $this->normDate($row['date_adhesion'] ?? null),
                    'baptise_par' => $this->normStr($row['officiant_bapteme'] ?? null),
                    'observations' => $this->buildObservations($row),
                ]
            );
        }
    }

    private function normStr(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    private function normDate(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        if ($s === '' || str_starts_with($s, '0000-00-00')) {
            return null;
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $s, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function buildAdresses(array $row): ?string
    {
        $lines = [];
        $nationalite = $this->normStr($row['nationalite'] ?? null);
        if ($nationalite !== null) {
            $lines[] = 'Nationalité : '.$nationalite;
        }
        $adresse = $this->normStr($row['adresse'] ?? null);
        if ($adresse !== null) {
            $lines[] = $adresse;
        }
        $ville = $this->normStr($row['ville'] ?? null);
        if ($ville !== null) {
            $lines[] = $ville;
        }
        $cp = $this->normStr($row['code_postal'] ?? null);
        if ($cp !== null) {
            $lines[] = 'Code postal : '.$cp;
        }
        $email = $this->normStr($row['email'] ?? null);
        if ($email !== null) {
            $lines[] = 'Courriel : '.$email;
        }

        return $lines === [] ? null : implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function buildTelephone(array $row): ?string
    {
        $a = $this->normStr($row['telephone'] ?? null);
        $b = $this->normStr($row['telephone2'] ?? null);
        if ($a !== null && $b !== null) {
            return $a.' / '.$b;
        }

        return $a ?? $b;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function buildObservations(array $row): ?string
    {
        $lines = [];
        $lines[] = 'Import SQL membres — source_id '.($row['source_id'] ?? '?').'.';
        $num = $this->normStr($row['numero_membre'] ?? null);
        if ($num !== null) {
            $lines[] = 'N° membre (historique) : '.$num.'.';
        }
        $statut = $this->normStr($row['statut'] ?? null);
        if ($statut !== null) {
            $lines[] = 'Statut (historique) : '.$statut.'.';
        }
        $mode = $this->normStr($row['mode_adhesion'] ?? null);
        if ($mode !== null) {
            $lines[] = 'Mode d’adhésion (historique) : '.$mode.'.';
        }
        $notes = $this->normStr($row['notes'] ?? null);
        if ($notes !== null) {
            $lines[] = 'Notes : '.$notes;
        }
        $photo = $this->normStr($row['photo'] ?? null);
        if ($photo !== null) {
            $lines[] = 'Photo (réf. source) : '.$photo;
        }

        return implode("\n", $lines);
    }
}
