<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down(): void
    {
        if (Schema::hasTable('lignes_dime_offrande_recap')) {
            $toDrop = [];
            foreach (['designation', 'mode_don', 'destination_don', 'quantite_nature', 'unite_nature'] as $col) {
                if (Schema::hasColumn('lignes_dime_offrande_recap', $col)) {
                    $toDrop[] = $col;
                }
            }
            if ($toDrop !== []) {
                Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) use ($toDrop) {
                    $table->dropColumn($toDrop);
                });
            }
        }

        if (Schema::hasTable('types_recette_mission')) {
            $toDrop = [];
            foreach (['exclure_rapport_mission', 'mission_sans_partage'] as $col) {
                if (Schema::hasColumn('types_recette_mission', $col)) {
                    $toDrop[] = $col;
                }
            }
            if ($toDrop !== []) {
                Schema::table('types_recette_mission', function (Blueprint $table) use ($toDrop) {
                    $table->dropColumn($toDrop);
                });
            }
        }
    }
};
