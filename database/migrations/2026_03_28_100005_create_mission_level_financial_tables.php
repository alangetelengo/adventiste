<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrees_financieres_groupe_mission', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('groupe_mission_id')
                ->constrained('groupes_mission', 'id', 'fk_efgm_groupe')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');

            $table->decimal('dimes', 15, 2)->default(0);
            $table->decimal('offrande_ecole_sabbat', 15, 2)->default(0);
            $table->decimal('offrande_budget_eglise', 15, 2)->default(0);
            $table->decimal('offrande_fonds_mission', 15, 2)->default(0);
            $table->decimal('offrande_autres', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['groupe_mission_id', 'annee', 'mois'], 'uq_efgm_groupe_periode');
        });

        Schema::create('rapports_station_mission', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');
            $table->timestamp('dernier_remplissage_auto_le')->nullable();
            $table->timestamps();

            $table->unique(['mission_id', 'annee', 'mois'], 'uq_rsm_mission_periode');
        });

        Schema::create('lignes_rapport_station_mission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_station_mission_id')
                ->constrained('rapports_station_mission', 'id', 'fk_lrsm_rsm')
                ->cascadeOnDelete();
            $table->string('code_ligne', 128);
            $table->decimal('pourcentage', 8, 4)->nullable();
            $table->decimal('montant_mois', 15, 2)->default(0);
            $table->decimal('montant_periode_precedente', 15, 2)->default(0);
            $table->decimal('montant_cumule', 15, 2)->default(0);
            $table->boolean('montant_mois_saisi_manuel')->default(false);
            $table->unsignedSmallInteger('ordre_tri')->default(0);
            $table->timestamps();

            $table->unique(['rapport_station_mission_id', 'code_ligne'], 'uq_lrsm_code');
        });

        Schema::create('lignes_autres_dimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_station_mission_id')
                ->constrained('rapports_station_mission', 'id', 'fk_lad_rsm')
                ->cascadeOnDelete();
            $table->string('code_categorie', 64);
            $table->decimal('montant_mois', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['rapport_station_mission_id', 'code_categorie'], 'uq_lad_rsm_cat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_autres_dimes');
        Schema::dropIfExists('lignes_rapport_station_mission');
        Schema::dropIfExists('rapports_station_mission');
        Schema::dropIfExists('entrees_financieres_groupe_mission');
    }
};
