<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eglises_locales', function (Blueprint $table) {
            $table->json('indicateurs_financiers')->nullable()->after('actif');
        });
    }

    public function down(): void
    {
        Schema::table('eglises_locales', function (Blueprint $table) {
            $table->dropColumn('indicateurs_financiers');
        });
    }
};
