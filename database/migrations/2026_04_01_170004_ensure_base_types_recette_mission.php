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
        $missionIds = DB::table('missions')->pluck('id');

        foreach ($missionIds as $missionId) {
            $rows = [
                [
                    'code' => 'dime',
                    'libelle' => 'Dîme',
                    'categorie' => 'dime',
                    'ordre' => 10,
                    'actif' => true,
                    'exclure_rapport_mission' => false,
                    'mission_sans_partage' => false,
                ],
                [
                    'code' => 'offrande_cultuelle',
                    'libelle' => 'Offrande culte (collecte du culte)',
                    'categorie' => 'offrande',
                    'ordre' => 20,
                    'actif' => true,
                    'exclure_rapport_mission' => false,
                    'mission_sans_partage' => false,
                ],
                [
                    'code' => 'don',
                    'libelle' => 'Don',
                    'categorie' => 'don',
                    'ordre' => 30,
                    'actif' => true,
                    'exclure_rapport_mission' => false,
                    'mission_sans_partage' => false,
                ],
            ];

            foreach ($rows as $row) {
                DB::table('types_recette_mission')->updateOrInsert(
                    ['mission_id' => $missionId, 'code' => $row['code']],
                    array_merge($row, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
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
            ->whereIn('code', ['dime', 'offrande_cultuelle', 'don'])
            ->delete();
    }
};
