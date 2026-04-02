<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->string('type_revenu', 16)->nullable()->after('nom_visiteur');
        });

        foreach (DB::table('lignes_dime_offrande_recap')->cursor() as $row) {
            $d = (float) $row->dimes;
            $o = (float) $row->offrandes;
            $type = null;
            if ($d > 0 && $o <= 0) {
                $type = 'dime';
            } elseif ($o > 0 && $d <= 0) {
                $type = 'offrande';
            } elseif ($d > 0 && $o > 0) {
                $type = 'dime';
            }
            if ($type !== null) {
                DB::table('lignes_dime_offrande_recap')->where('id', $row->id)->update(['type_revenu' => $type]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('lignes_dime_offrande_recap', function (Blueprint $table) {
            $table->dropColumn('type_revenu');
        });
    }
};
