<?php

use App\Models\RapportMensuelEglise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapports_mensuels_eglise', function (Blueprint $table) {
            $table->string('etat_transmission', 24)
                ->default(RapportMensuelEglise::ETAT_BROUILLON)
                ->after('etabli_le');
            $table->timestamp('soumis_le')->nullable()->after('etat_transmission');
            $table->foreignId('soumis_par_user_id')->nullable()->after('soumis_le')->constrained('users')->nullOnDelete();
            $table->timestamp('mission_revu_le')->nullable()->after('soumis_par_user_id');
            $table->foreignId('mission_revu_par_user_id')->nullable()->after('mission_revu_le')->constrained('users')->nullOnDelete();
            $table->text('mission_commentaire')->nullable()->after('mission_revu_par_user_id');
            $table->index(['etat_transmission', 'annee', 'mois'], 'idx_rme_transmission_periode');
        });

        DB::table('rapports_mensuels_eglise')
            ->whereNull('etat_transmission')
            ->update(['etat_transmission' => RapportMensuelEglise::ETAT_BROUILLON]);
    }

    public function down(): void
    {
        Schema::table('rapports_mensuels_eglise', function (Blueprint $table) {
            $table->dropIndex('idx_rme_transmission_periode');
            $table->dropConstrainedForeignId('mission_revu_par_user_id');
            $table->dropColumn('mission_revu_le');
            $table->dropConstrainedForeignId('soumis_par_user_id');
            $table->dropColumn('soumis_le');
            $table->dropColumn('etat_transmission');
            $table->dropColumn('mission_commentaire');
        });
    }
};
