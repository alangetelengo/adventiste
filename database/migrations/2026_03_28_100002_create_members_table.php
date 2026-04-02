<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membres', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('eglise_locale_id')->nullable()->constrained('eglises_locales')->cascadeOnDelete();
            $table->foreignId('groupe_mission_id')->nullable()->constrained('groupes_mission')->cascadeOnDelete();

            $table->string('nom');
            $table->string('prenom');
            $table->string('sexe', 32)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('noms_pere')->nullable();
            $table->string('noms_mere')->nullable();
            $table->text('adresses')->nullable();
            $table->string('telephone', 64)->nullable();
            $table->string('niveau_etudes')->nullable();
            $table->string('occupation')->nullable();
            $table->string('situation_matrimoniale', 64)->nullable();
            $table->date('date_mariage')->nullable();
            $table->string('conjoint')->nullable();
            $table->date('date_bapteme')->nullable();
            $table->string('lieu_bapteme')->nullable();
            $table->string('religion_anterieure')->nullable();
            $table->string('recu_dans_eglise_de')->nullable();
            $table->date('recu_le')->nullable();
            $table->string('baptise_par')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();

            $table->index(['eglise_locale_id', 'nom', 'prenom']);
            $table->index(['groupe_mission_id', 'nom', 'prenom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres');
    }
};
