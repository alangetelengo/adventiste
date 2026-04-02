<?php

use App\Models\RapportMembreEglise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports_membres_eglise', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('eglise_locale_id')->constrained('eglises_locales')->cascadeOnDelete();

            $table->string('type_periode', 16)->default(RapportMembreEglise::TYPE_MENSUEL);
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois')->default(0);
            $table->string('etat', 16)->default(RapportMembreEglise::ETAT_BROUILLON);

            $table->timestamp('soumis_le')->nullable();
            $table->foreignId('soumis_par_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revu_le')->nullable();
            $table->foreignId('revu_par_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire_mission')->nullable();

            $table->unsignedInteger('total_membres')->default(0);
            $table->unsignedInteger('total_baptemes_immersion')->default(0);
            $table->unsignedInteger('total_baptemes_profession_foi')->default(0);
            $table->unsignedInteger('total_entrees_transfert')->default(0);
            $table->unsignedInteger('total_changements_statut')->default(0);
            $table->json('stats_statuts')->nullable();
            $table->text('notes_locales')->nullable();

            $table->timestamps();

            $table->unique(['eglise_locale_id', 'type_periode', 'annee', 'mois'], 'uq_rapport_membres_eglise_periode');
            $table->index(['etat', 'type_periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports_membres_eglise');
    }
};
