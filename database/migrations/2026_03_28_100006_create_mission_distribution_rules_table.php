<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regles_repartition_mission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->string('code_ligne', 128);
            $table->string('libelle_fr');
            $table->string('s_applique_a', 32);
            $table->decimal('pourcentage', 8, 4)->nullable();
            $table->string('code_ligne_parent', 128)->nullable();
            $table->unsignedSmallInteger('ordre_tri')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['mission_id', 's_applique_a', 'actif'], 'idx_rrm_mission_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_repartition_mission');
    }
};
