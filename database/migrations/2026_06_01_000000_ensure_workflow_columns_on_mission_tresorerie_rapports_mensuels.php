<?php

use App\Models\MissionTresorerieRapportMensuel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'mission_tresorerie_rapports_mensuels';

    public function up(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        if (! Schema::hasColumn($this->table, 'etat_transmission')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('etat_transmission', 32)
                    ->default(MissionTresorerieRapportMensuel::ETAT_BROUILLON)
                    ->after('offrandes_mois');
            });
        }

        if (! Schema::hasColumn($this->table, 'soumis_le')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->timestamp('soumis_le')->nullable()->after('etat_transmission');
            });
        }

        if (! Schema::hasColumn($this->table, 'soumis_par_user_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unsignedBigInteger('soumis_par_user_id')->nullable()->after('soumis_le');
            });
        }

        if (! Schema::hasColumn($this->table, 'mission_revu_le')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->timestamp('mission_revu_le')->nullable()->after('soumis_par_user_id');
            });
        }

        if (! Schema::hasColumn($this->table, 'mission_revu_par_user_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unsignedBigInteger('mission_revu_par_user_id')->nullable()->after('mission_revu_le');
            });
        }

        if (! Schema::hasColumn($this->table, 'mission_commentaire')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('mission_commentaire')->nullable()->after('mission_revu_par_user_id');
            });
        }

        if (Schema::hasColumn($this->table, 'etat_transmission')) {
            DB::table($this->table)
                ->whereNull('etat_transmission')
                ->update(['etat_transmission' => MissionTresorerieRapportMensuel::ETAT_BROUILLON]);
        }
    }

    public function down(): void
    {
        // no-op: migration de rattrapage purement idempotente.
    }
};
