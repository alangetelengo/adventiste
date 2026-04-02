<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_tresorerie_transferts_bancaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('mois');
            $table->decimal('montant_transfere', 15, 2)->default(0);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['mission_id', 'annee', 'mois'], 'uq_mttb_mission_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_tresorerie_transferts_bancaires');
    }
};
