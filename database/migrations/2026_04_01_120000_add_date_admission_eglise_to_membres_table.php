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
            $table->date('date_admission_eglise')->nullable()->after('recu_le');
        });

        DB::table('membres')
            ->where('mode_entree', 'bapteme')
            ->whereNull('date_admission_eglise')
            ->whereNotNull('date_bapteme')
            ->update(['date_admission_eglise' => DB::raw('date_bapteme')]);
    }

    public function down(): void
    {
        Schema::table('membres', function (Blueprint $table) {
            $table->dropColumn('date_admission_eglise');
        });
    }
};
