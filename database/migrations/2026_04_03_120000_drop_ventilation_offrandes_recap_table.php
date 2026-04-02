<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ventilation_offrandes_recap');
    }

    public function down(): void
    {
        Schema::create('ventilation_offrandes_recap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recap_sabbat_eglise_id')
                ->unique()
                ->constrained('recaps_sabbat_eglise', 'id', 'fk_vor_recap')
                ->cascadeOnDelete();
            $table->decimal('eds_fonds_placement', 15, 2)->default(0);
            $table->decimal('eds_anniversaire_remerciement', 15, 2)->default(0);
            $table->decimal('eds_ecole_sabbat', 15, 2)->default(0);
            $table->decimal('eds_13e_sabbat', 15, 2)->default(0);
            $table->decimal('offrandes_enveloppes', 15, 2)->default(0);
            $table->decimal('offrandes_culte', 15, 2)->default(0);
            $table->decimal('offrandes_construction', 15, 2)->default(0);
            $table->timestamps();
        });
    }
};
