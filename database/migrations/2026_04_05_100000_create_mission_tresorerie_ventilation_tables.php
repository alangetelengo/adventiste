<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_tresorerie_ventilation_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->string('code', 64)->nullable();
            $table->string('designation');
            $table->string('kind', 32);
            $table->decimal('pourcentage', 10, 4)->nullable();
            $table->json('somme_codes')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->unique(['mission_id', 'code'], 'uq_mtv_ligne_mission_code');
            $table->index(['mission_id', 'ordre'], 'idx_mtv_ligne_mission_ordre');
        });

        Schema::create('mission_tresorerie_rapports_mensuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');
            $table->decimal('dimes_eglises', 15, 2)->default(0);
            $table->decimal('autres_dimes', 15, 2)->default(0);
            $table->decimal('offrandes_mois', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['mission_id', 'annee', 'mois'], 'uq_mtrm_mission_periode');
        });

        Schema::create('mission_tresorerie_rapport_ligne_montants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_id')
                ->constrained('mission_tresorerie_rapports_mensuels', 'id', 'fk_mtrlm_rapport')
                ->cascadeOnDelete();
            $table->foreignId('ligne_id')
                ->constrained('mission_tresorerie_ventilation_lignes', 'id', 'fk_mtrlm_ligne')
                ->cascadeOnDelete();
            $table->decimal('montant_mois', 15, 2)->default(0);
            $table->decimal('montant_cumule', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['rapport_id', 'ligne_id'], 'uq_mtrlm_rapport_ligne');
        });

        $now = now();
        foreach (DB::table('missions')->pluck('id') as $missionId) {
            $this->seedLignesPourMission((int) $missionId, $now);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_tresorerie_rapport_ligne_montants');
        Schema::dropIfExists('mission_tresorerie_rapports_mensuels');
        Schema::dropIfExists('mission_tresorerie_ventilation_lignes');
    }

    private function seedLignesPourMission(int $missionId, \Illuminate\Support\Carbon $now): void
    {
        $rows = [
            ['code' => null, 'kind' => 'titre', 'designation' => 'Ventilation des dîmes', 'pourcentage' => null, 'somme_codes' => null, 'ordre' => 10],
            ['code' => 'dime_union', 'kind' => 'pourcentage_dimes', 'designation' => 'Dîme de la dîme (Union)', 'pourcentage' => 8, 'somme_codes' => null, 'ordre' => 20],
            ['code' => 'fonds_cg', 'kind' => 'pourcentage_dimes', 'designation' => 'Fonds conf. générale (GC)', 'pourcentage' => 2.4, 'somme_codes' => null, 'ordre' => 30],
            ['code' => 'fonds_dao_inst', 'kind' => 'pourcentage_dimes', 'designation' => 'Fonds institutions DAO', 'pourcentage' => 2, 'somme_codes' => null, 'ordre' => 40],
            ['code' => 'fonds_retraites', 'kind' => 'pourcentage_dimes', 'designation' => 'Fonds de retraites DAO', 'pourcentage' => 11, 'somme_codes' => null, 'ordre' => 50],
            ['code' => 'fonds_dime_partagee', 'kind' => 'pourcentage_dimes', 'designation' => 'Fonds dîme partagée DAO', 'pourcentage' => 7.6, 'somme_codes' => null, 'ordre' => 60],
            ['code' => 'total_pct_dime', 'kind' => 'somme_codes', 'designation' => 'Total pourcentage de dîme (GC / DAO)', 'pourcentage' => null, 'somme_codes' => json_encode(['fonds_cg', 'fonds_dao_inst', 'fonds_retraites', 'fonds_dime_partagee']), 'ordre' => 70],
            ['code' => null, 'kind' => 'titre', 'designation' => 'Répartition des offrandes', 'pourcentage' => null, 'somme_codes' => null, 'ordre' => 80],
            ['code' => 'off_cg', 'kind' => 'pourcentage_offrandes', 'designation' => 'Fonds champs mondiale — CG', 'pourcentage' => 20, 'somme_codes' => null, 'ordre' => 90],
            ['code' => 'off_dao', 'kind' => 'pourcentage_offrandes', 'designation' => 'Fonds offrande — DAO', 'pourcentage' => 5, 'somme_codes' => null, 'ordre' => 100],
            ['code' => 'off_umac', 'kind' => 'pourcentage_offrandes', 'designation' => 'Fonds offrande — UMAC (Union)', 'pourcentage' => 5, 'somme_codes' => null, 'ordre' => 110],
            ['code' => 'total_off_haut', 'kind' => 'somme_codes', 'designation' => 'Sous-total (CG, DAO & UMAC)', 'pourcentage' => null, 'somme_codes' => json_encode(['off_cg', 'off_dao', 'off_umac']), 'ordre' => 120],
            ['code' => 'off_mission', 'kind' => 'pourcentage_offrandes', 'designation' => 'Répartition offrande — Mission / Fédération', 'pourcentage' => 20, 'somme_codes' => null, 'ordre' => 130],
            ['code' => 'off_locale', 'kind' => 'pourcentage_offrandes', 'designation' => 'Répartition offrande — Église locale', 'pourcentage' => 50, 'somme_codes' => null, 'ordre' => 140],
            ['code' => null, 'kind' => 'titre', 'designation' => 'Synthèse remontée (GC — DAO — UMAC)', 'pourcentage' => null, 'somme_codes' => null, 'ordre' => 150],
            ['code' => 'total_rapport', 'kind' => 'somme_codes', 'designation' => 'Total rapport (dîme Union + total % dîme + off. haut)', 'pourcentage' => null, 'somme_codes' => json_encode(['dime_union', 'total_pct_dime', 'total_off_haut']), 'ordre' => 160],
        ];

        foreach ($rows as $row) {
            DB::table('mission_tresorerie_ventilation_lignes')->insert(array_merge($row, [
                'mission_id' => $missionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
};
