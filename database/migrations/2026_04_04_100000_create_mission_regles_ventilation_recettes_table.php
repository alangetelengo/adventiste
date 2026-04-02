<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_regles_ventilation_recettes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')
                ->unique()
                ->constrained('missions')
                ->cascadeOnDelete();
            /** Pourcentage du montant versé à la mission (le reste reste au budget église locale). */
            $table->decimal('part_mission_dime_pct', 5, 2)->default(100);
            $table->decimal('part_mission_offrande_pct', 5, 2)->default(50);
            $table->decimal('part_mission_don_pct', 5, 2)->default(0);
            $table->string('libelle_rapport_dime', 120)->nullable();
            $table->string('libelle_rapport_offrande', 120)->nullable();
            $table->string('libelle_rapport_don', 120)->nullable();
            $table->text('notes_internes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_regles_ventilation_recettes');
    }
};
