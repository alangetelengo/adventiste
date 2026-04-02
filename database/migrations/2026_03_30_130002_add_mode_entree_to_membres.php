<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membres', function (Blueprint $table) {
            $table->string('mode_entree', 32)->nullable()->after('groupe_mission_id');
            $table->string('type_bapteme_entree', 32)->nullable()->after('mode_entree');
            $table->index(['eglise_locale_id', 'mode_entree']);
        });

        DB::table('membres')
            ->whereNotNull('date_bapteme')
            ->whereNull('mode_entree')
            ->update(['mode_entree' => 'bapteme']);

        DB::table('membres')
            ->whereNull('mode_entree')
            ->where(function ($q) {
                $q->whereNotNull('recu_dans_eglise_de')
                    ->orWhereNotNull('recu_le');
            })
            ->update(['mode_entree' => 'transfert']);
    }

    public function down(): void
    {
        Schema::table('membres', function (Blueprint $table) {
            $table->dropIndex(['eglise_locale_id', 'mode_entree']);
            $table->dropColumn(['mode_entree', 'type_bapteme_entree']);
        });
    }
};

