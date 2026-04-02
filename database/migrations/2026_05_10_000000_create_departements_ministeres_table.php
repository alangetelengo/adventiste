<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departements_ministeres', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('eglise_locale_id')
                ->constrained('eglises_locales', 'id', 'fk_dm_eglise')
                ->cascadeOnDelete();
            $table->string('nom', 255);
            $table->string('code_unique', 64)->unique();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['eglise_locale_id', 'code_unique'], 'uq_dm_eglise_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departements_ministeres');
    }
};
