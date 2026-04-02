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

        if (! $this->indexExists($this->table, 'idx_mtrm_mission_etat')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->index(['mission_id', 'etat_transmission'], 'idx_mtrm_mission_etat');
            });
        }

        if (Schema::hasColumn($this->table, 'soumis_par_user_id') && ! $this->foreignKeyExists($this->table, 'fk_mtrm_soumis_user')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->foreign('soumis_par_user_id', 'fk_mtrm_soumis_user')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn($this->table, 'mission_revu_par_user_id') && ! $this->foreignKeyExists($this->table, 'fk_mtrm_revu_user')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->foreign('mission_revu_par_user_id', 'fk_mtrm_revu_user')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
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
        if (! Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            if ($this->foreignKeyExists($this->table, 'fk_mtrm_soumis_user')) {
                $table->dropForeign('fk_mtrm_soumis_user');
            }
            if ($this->foreignKeyExists($this->table, 'fk_mtrm_revu_user')) {
                $table->dropForeign('fk_mtrm_revu_user');
            }
            if ($this->indexExists($this->table, 'idx_mtrm_mission_etat')) {
                $table->dropIndex('idx_mtrm_mission_etat');
            }
        });

        $dropColumns = [];
        foreach ([
            'soumis_par_user_id',
            'mission_revu_par_user_id',
            'etat_transmission',
            'soumis_le',
            'mission_revu_le',
            'mission_commentaire',
        ] as $column) {
            if (Schema::hasColumn($this->table, $column)) {
                $dropColumns[] = $column;
            }
        }

        if ($dropColumns !== []) {
            Schema::table($this->table, function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return DB::table('information_schema.table_constraints')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
