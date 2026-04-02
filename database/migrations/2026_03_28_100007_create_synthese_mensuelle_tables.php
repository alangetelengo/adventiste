<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapports_mensuels_eglise', function (Blueprint $table) {
            $table->string('etabli_a')->nullable()->after('pieces_jointes_signatures');
            $table->date('etabli_le')->nullable()->after('etabli_a');
        });

        Schema::create('lignes_synthese_mensuelle_eglise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_mensuel_eglise_id')
                ->constrained('rapports_mensuels_eglise', 'id', 'fk_lse_rme')
                ->cascadeOnDelete();
            /** 1 à 5 = sabbat dans le mois ; 0 = ligne « Total général » du mois */
            $table->unsignedTinyInteger('indice_sabbat');
            $table->date('date_sabbat')->nullable();

            $table->decimal('dimes', 15, 2)->default(0);
            $table->decimal('offrande_ecole_sabbat', 15, 2)->default(0);
            $table->decimal('budget_eglise_locale', 15, 2)->default(0);
            $table->decimal('fonds_missionnaires', 15, 2)->default(0);
            $table->decimal('autres_offrandes', 15, 2)->default(0);
            /** Somme des cinq colonnes (cache pour PDF / contrôle) */
            $table->decimal('montant_total', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['rapport_mensuel_eglise_id', 'indice_sabbat'], 'uq_lse_rme_indice');
        });

        Schema::create('aggregats_synthese_mission_mensuelle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');

            $table->decimal('dimes', 15, 2)->default(0);
            $table->decimal('offrande_ecole_sabbat', 15, 2)->default(0);
            $table->decimal('budget_eglise_locale', 15, 2)->default(0);
            $table->decimal('fonds_missionnaires', 15, 2)->default(0);
            $table->decimal('autres_offrandes', 15, 2)->default(0);
            $table->decimal('montant_total', 15, 2)->default(0);

            $table->timestamp('calcule_le')->nullable();
            $table->timestamps();

            $table->unique(['mission_id', 'annee', 'mois'], 'uq_asm_mission_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aggregats_synthese_mission_mensuelle');
        Schema::dropIfExists('lignes_synthese_mensuelle_eglise');

        Schema::table('rapports_mensuels_eglise', function (Blueprint $table) {
            $table->dropColumn(['etabli_a', 'etabli_le']);
        });
    }
};
