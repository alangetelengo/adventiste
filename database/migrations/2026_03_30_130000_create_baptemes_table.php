<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baptemes', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifiant_public')->unique();
            $table->foreignId('eglise_locale_id')->constrained('eglises_locales')->cascadeOnDelete();
            $table->foreignId('membre_id')->nullable()->constrained('membres')->nullOnDelete();
            $table->string('nom');
            $table->string('prenom');
            $table->string('type_bapteme', 32);
            $table->date('date_bapteme');
            $table->string('lieu_bapteme')->nullable();
            $table->string('officiant')->nullable();
            $table->string('numero_registre', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['eglise_locale_id', 'date_bapteme']);
            $table->index(['membre_id', 'date_bapteme']);
            $table->unique(['eglise_locale_id', 'numero_registre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baptemes');
    }
};

