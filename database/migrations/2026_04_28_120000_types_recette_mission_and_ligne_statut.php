<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_recette_mission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('libelle');
            $table->string('categorie', 16);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['mission_id', 'code'], 'uq_trm_mission_code');
            $table->index(['mission_id', 'ordre'], 'idx_trm_mission_ordre');
        });

        Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->foreignId('type_recette_id')
                ->nullable()
                ->after('recap_sabbat_eglise_id')
                ->constrained('types_recette_mission', 'id', 'fk_ldr_type_recette')
                ->nullOnDelete();
            $table->string('origine', 16)->default('individuel')->after('nom_visiteur');
            $table->string('statut_ligne', 16)->default('brouillon')->after('ordre_ligne');
        });

        $now = now();
        $missionIds = DB::table('missions')->pluck('id');
        foreach ($missionIds as $missionId) {
            $mid = (int) $missionId;
            $tidDime = DB::table('types_recette_mission')->insertGetId([
                'mission_id' => $mid,
                'code' => 'dime',
                'libelle' => 'Dîme',
                'categorie' => 'dime',
                'ordre' => 10,
                'actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $tidOff = DB::table('types_recette_mission')->insertGetId([
                'mission_id' => $mid,
                'code' => 'offrande_cultuelle',
                'libelle' => 'Offrande cultuelle',
                'categorie' => 'offrande',
                'ordre' => 20,
                'actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $tidDon = DB::table('types_recette_mission')->insertGetId([
                'mission_id' => $mid,
                'code' => 'don',
                'libelle' => 'Don',
                'categorie' => 'don',
                'ordre' => 30,
                'actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $map = [
                'dime' => $tidDime,
                'offrande' => $tidOff,
                'don' => $tidDon,
            ];

            $egliseIds = DB::table('eglises_locales')->where('mission_id', $mid)->pluck('id');
            foreach ($egliseIds as $egliseId) {
                $recapIds = DB::table('recaps_sabbat_eglise')->where('eglise_locale_id', $egliseId)->pluck('id');
                foreach ($recapIds as $rid) {
                    $lignes = DB::table('lignes_dime_offrande_recap')->where('recap_sabbat_eglise_id', $rid)->get();
                    foreach ($lignes as $ligne) {
                        $tr = (string) ($ligne->type_revenu ?? 'offrande');
                        $tid = $map[$tr] ?? $tidOff;
                        DB::table('lignes_dime_offrande_recap')->where('id', $ligne->id)->update([
                            'type_recette_id' => $tid,
                            'origine' => 'individuel',
                            'statut_ligne' => 'verrouille',
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->dropForeign('fk_ldr_type_recette');
            $table->dropColumn(['type_recette_id', 'origine', 'statut_ligne']);
        });

        Schema::dropIfExists('types_recette_mission');
    }
};
