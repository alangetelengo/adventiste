<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports_mensuels_eglise', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('eglise_locale_id')
                ->constrained('eglises_locales', 'id', 'fk_rme_eglise')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');
            $table->timestamp('regenere_le')->nullable();
            $table->timestamp('verrouille_le')->nullable();

            $table->decimal('total_dimes_mois', 15, 2)->default(0);
            $table->decimal('total_moitie_offrandes_mois', 15, 2)->default(0);
            $table->decimal('total_autres_offrandes_mission_mois', 15, 2)->default(0);
            $table->decimal('total_a_transferer_mission_mois', 15, 2)->default(0);

            $table->boolean('signe_tresorier')->default(false);
            $table->date('date_signature_tresorier')->nullable();
            $table->boolean('signe_pasteur')->default(false);
            $table->date('date_signature_pasteur')->nullable();
            $table->boolean('signe_secretaire')->default(false);
            $table->date('date_signature_secretaire')->nullable();
            $table->json('pieces_jointes_signatures')->nullable();

            $table->timestamps();

            $table->unique(['eglise_locale_id', 'annee', 'mois'], 'uq_rme_eglise_periode');
        });

        Schema::create('rapports_mensuels_lignes_sabbat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_mensuel_eglise_id')
                ->constrained('rapports_mensuels_eglise', 'id', 'fk_rmls_rme')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('indice_sabbat_dans_mois');
            $table->date('date_sabbat')->nullable();
            $table->decimal('total_dimes', 15, 2)->default(0);
            $table->decimal('moitie_offrandes', 15, 2)->default(0);
            $table->decimal('autres_offrandes_mission', 15, 2)->default(0);
            $table->decimal('total_transferer_mission', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['rapport_mensuel_eglise_id', 'indice_sabbat_dans_mois'], 'uq_rmls_rme_sabbat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports_mensuels_lignes_sabbat');
        Schema::dropIfExists('rapports_mensuels_eglise');
    }
};
