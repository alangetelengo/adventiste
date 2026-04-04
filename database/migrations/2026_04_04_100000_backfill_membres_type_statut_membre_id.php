<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renseigne type_statut_membre_id pour les membres importés ou créés avant la gestion des statuts,
 * afin que la liste /membres affiche le libellé (Régulier, Actif, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::select(
            'SELECT m.id, m.mode_entree, m.actif, m.date_bapteme, m.recu_le, e.mission_id AS mission_id
             FROM membres m
             INNER JOIN eglises_locales e ON e.id = m.eglise_locale_id
             WHERE m.type_statut_membre_id IS NULL'
        );

        foreach ($rows as $row) {
            $missionId = (int) $row->mission_id;
            if ($missionId <= 0) {
                continue;
            }

            $code = $this->codeStatutCible($row);

            $typeId = DB::table('types_statut_membres')
                ->where('mission_id', $missionId)
                ->where('code', $code)
                ->where('actif', true)
                ->value('id');

            if ($typeId === null && $code === 'regulier') {
                $typeId = DB::table('types_statut_membres')
                    ->where('mission_id', $missionId)
                    ->where('code', 'actif')
                    ->where('actif', true)
                    ->value('id');
            }

            if ($typeId === null) {
                continue;
            }

            $typeCode = DB::table('types_statut_membres')->where('id', $typeId)->value('code');
            $membreActif = $typeCode !== 'refroidi';

            DB::table('membres')->where('id', $row->id)->update([
                'type_statut_membre_id' => $typeId,
                'actif' => $membreActif,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param  object{id: int|string, mode_entree: ?string, actif: int|bool, date_bapteme: ?string, recu_le: ?string}  $row
     */
    private function codeStatutCible(object $row): string
    {
        $mode = $row->mode_entree;

        if ($mode === 'bapteme') {
            return 'regulier';
        }
        if ($mode === 'transfert') {
            return 'actif';
        }

        if ($row->date_bapteme !== null && $row->date_bapteme !== '') {
            return 'regulier';
        }
        if ($row->recu_le !== null && $row->recu_le !== '') {
            return 'actif';
        }

        return filter_var($row->actif, FILTER_VALIDATE_BOOLEAN) ? 'actif' : 'refroidi';
    }

    public function down(): void
    {
        // Pas de retour arrière : les statuts attribués peuvent avoir été ajustés manuellement ensuite.
    }
};
