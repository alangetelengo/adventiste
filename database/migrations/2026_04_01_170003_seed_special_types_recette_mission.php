<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('types_recette_mission')) {
            return;
        }

        $now = now();
        $missions = DB::table('missions')->pluck('id');

        foreach ($missions as $missionId) {
            $maxOrdre = (int) DB::table('types_recette_mission')
                ->where('mission_id', $missionId)
                ->max('ordre');

            $rows = [
                [
                    'code' => 'offrande_construction',
                    'libelle' => 'Offrande construction',
                    'categorie' => 'offrande',
                    'ordre' => max(40, $maxOrdre + 10),
                    'actif' => true,
                    'exclure_rapport_mission' => true,
                    'mission_sans_partage' => false,
                ],
                [
                    'code' => 'offrande_13e_sabbat',
                    'libelle' => 'Offrande 13e sabbat',
                    'categorie' => 'offrande',
                    'ordre' => max(50, $maxOrdre + 20),
                    'actif' => true,
                    'exclure_rapport_mission' => false,
                    'mission_sans_partage' => true,
                ],
            ];

            foreach ($rows as $row) {
                DB::table('types_recette_mission')->updateOrInsert(
                    ['mission_id' => $missionId, 'code' => $row['code']],
                    array_merge($row, ['updated_at' => $now, 'created_at' => $now])
                );
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('types_recette_mission')) {
            return;
        }

        DB::table('types_recette_mission')
            ->whereIn('code', ['offrande_construction', 'offrande_13e_sabbat'])
            ->delete();
    }
};
