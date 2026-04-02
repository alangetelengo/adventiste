<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->foreignId('departement_ministere_id')
                ->nullable()
                ->constrained('departements_ministeres', 'id', 'fk_ldor_dept')
                ->nullableOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->dropForeignIdFor('departements_ministeres', 'departement_ministere_id');
            $table->dropColumn('departement_ministere_id');
        });
    }
};
