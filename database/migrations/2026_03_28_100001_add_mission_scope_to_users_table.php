<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('mission_id')->nullable()->after('id')->constrained('missions')->nullOnDelete();
            $table->foreignId('eglise_locale_id')->nullable()->after('mission_id')->constrained('eglises_locales')->nullOnDelete();
            $table->string('role', 64)->nullable()->after('eglise_locale_id');
            $table->uuid('identifiant_public')->nullable()->unique()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eglise_locale_id');
            $table->dropConstrainedForeignId('mission_id');
            $table->dropColumn(['role', 'identifiant_public']);
        });
    }
};
