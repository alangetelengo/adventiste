<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('types_recette_mission')) {
            Schema::table('types_recette_mission', function (Blueprint $table) {
                if (! Schema::hasColumn('types_recette_mission', 'exclure_rapport_mission')) {
                    $table->boolean('exclure_rapport_mission')->default(false)->after('actif');
                }
                if (! Schema::hasColumn('types_recette_mission', 'mission_sans_partage')) {
                    $table->boolean('mission_sans_partage')->default(false)->after('exclure_rapport_mission');
                }
            });
        }

        if (Schema::hasTable('lignes_dime_offrande_recap')) {
            Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
                if (! Schema::hasColumn('lignes_dime_offrande_recap', 'designation')) {
                    $table->string('designation', 255)->nullable()->after('nom_visiteur');
                }
                if (! Schema::hasColumn('lignes_dime_offrande_recap', 'mode_don')) {
                    $table->string('mode_don', 16)->nullable()->after('designation');
                }
                if (! Schema::hasColumn('lignes_dime_offrande_recap', 'destination_don')) {
                    $table->string('destination_don', 16)->nullable()->after('mode_don');
                }
                if (! Schema::hasColumn('lignes_dime_offrande_recap', 'quantite_nature')) {
                    $table->decimal('quantite_nature', 12, 2)->nullable()->after('destination_don');
                }
                if (! Schema::hasColumn('lignes_dime_offrande_recap', 'unite_nature')) {
                    $table->string('unite_nature', 64)->nullable()->after('quantite_nature');
                }
            });
        }

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
                [
                    'code' => 'offrande_construction',
                    'libelle' => 'Offrande construction',
                    'categorie' => 'offrande',
                    'ordre' => 40,
                    'actif' => true,
                    'exclure_rapport_mission' => true,
                    'mission_sans_partage' => false,
                ],
                [
                    'code' => 'offrande_13e_sabbat',
                    'libelle' => 'Offrande 13e sabbat',
                    'categorie' => 'offrande',
                    'ordre' => 50,
                    'actif' => true,
                    'exclure_rapport_mission' => false,
                    'mission_sans_partage' => true,
                ],
            ];

            foreach ($rows as $row) {
                DB::table('types_recette_mission')->updateOrInsert(
                    ['mission_id' => $missionId, 'code' => $row['code']],
                    array_merge($row, ['created_at' => $now, 'updated_at' => $now])
                );
            }
        }
    }

    public function down(): void
    {
        // No-op volontaire: migration de synchronisation idempotente.
    }
};
