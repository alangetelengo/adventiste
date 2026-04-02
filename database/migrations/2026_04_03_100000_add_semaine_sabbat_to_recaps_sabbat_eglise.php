<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recaps_sabbat_eglise', function (Blueprint $table) {
            $table->unsignedTinyInteger('semaine_sabbat')->nullable()->after('mois');
        });
    }

    public function down(): void
    {
        Schema::table('recaps_sabbat_eglise', function (Blueprint $table) {
            $table->dropColumn('semaine_sabbat');
        });
    }
};
