<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recaps_sabbat_eglise', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('eglise_locale_id')
                ->constrained('eglises_locales', 'id', 'fk_rse_eglise')
                ->cascadeOnDelete();
            $table->date('date_sabbat');
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');
            $table->string('recu_numero_de', 64)->nullable();
            $table->string('recu_numero_a', 64)->nullable();
            $table->string('numero_formulaire', 64)->nullable();
            $table->string('statut', 32)->default('brouillon');

            $table->boolean('signe_ancien')->default(false);
            $table->date('date_signature_ancien')->nullable();
            $table->boolean('signe_diacre')->default(false);
            $table->date('date_signature_diacre')->nullable();
            $table->boolean('signe_tresorier')->default(false);
            $table->date('date_signature_tresorier')->nullable();
            $table->boolean('signe_chef_district')->default(false);
            $table->date('date_signature_chef_district')->nullable();
            $table->json('pieces_jointes_signatures')->nullable();

            $table->timestamps();

            $table->unique(['eglise_locale_id', 'date_sabbat'], 'uq_rse_eglise_date');
            $table->index(['eglise_locale_id', 'annee', 'mois'], 'idx_rse_eglise_periode');
        });

        Schema::create('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recap_sabbat_eglise_id')
                ->constrained('recaps_sabbat_eglise', 'id', 'fk_ldr_recap')
                ->cascadeOnDelete();
            $table->foreignId('membre_id')->nullable()->constrained('membres')->nullOnDelete();
            $table->string('nom_visiteur')->nullable();
            $table->decimal('dimes', 15, 2)->default(0);
            $table->decimal('offrandes', 15, 2)->default(0);
            $table->unsignedSmallInteger('ordre_ligne')->default(0);
            $table->timestamps();
        });

        Schema::create('ventilation_offrandes_recap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recap_sabbat_eglise_id')
                ->unique()
                ->constrained('recaps_sabbat_eglise', 'id', 'fk_vor_recap')
                ->cascadeOnDelete();

            $table->decimal('eds_fonds_placement', 15, 2)->default(0);
            $table->decimal('eds_anniversaire_remerciement', 15, 2)->default(0);
            $table->decimal('eds_ecole_sabbat', 15, 2)->default(0);
            $table->decimal('eds_13e_sabbat', 15, 2)->default(0);

            $table->decimal('offrandes_enveloppes', 15, 2)->default(0);
            $table->decimal('offrandes_culte', 15, 2)->default(0);
            $table->decimal('offrandes_construction', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventilation_offrandes_recap');
        Schema::dropIfExists('lignes_dime_offrande_recap');
        Schema::dropIfExists('recaps_sabbat_eglise');
    }
};
