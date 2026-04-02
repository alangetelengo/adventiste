<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_statut_membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('libelle', 120);
            $table->string('description', 255)->nullable();
            $table->string('couleur', 20)->nullable();
            $table->unsignedSmallInteger('ordre')->default(100);
            $table->boolean('actif')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['mission_id', 'code']);
            $table->index(['mission_id', 'actif', 'ordre']);
        });

        Schema::table('membres', function (Blueprint $table) {
            $table->foreignId('type_statut_membre_id')
                ->nullable()
                ->after('groupe_mission_id')
                ->constrained('types_statut_membres')
                ->nullOnDelete();
        });

        Schema::create('membre_historique_statuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $table->foreignId('type_statut_membre_id')->constrained('types_statut_membres')->cascadeOnDelete();
            $table->foreignId('change_par_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motif')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['membre_id', 'changed_at']);
        });

        $now = now();
        $missions = DB::table('missions')->select('id')->get();
        foreach ($missions as $mission) {
            $types = [
                ['code' => 'actif', 'libelle' => 'Actif', 'description' => 'Membre actif de l\'église locale.', 'couleur' => '#16a34a', 'ordre' => 10],
                ['code' => 'regulier', 'libelle' => 'Régulier', 'description' => 'Membre en règle selon les principes de foi et la discipline ecclésiale.', 'couleur' => '#2563eb', 'ordre' => 20],
                ['code' => 'irregulier', 'libelle' => 'Irrégulier', 'description' => 'Situation nécessitant un accompagnement pastoral.', 'couleur' => '#ea580c', 'ordre' => 30],
                ['code' => 'sous_censure', 'libelle' => 'Sous censure', 'description' => 'Mesure disciplinaire appliquée selon le manuel de l\'Église.', 'couleur' => '#b91c1c', 'ordre' => 40],
                ['code' => 'refroidi', 'libelle' => 'Refroidi', 'description' => 'Membre absent durablement de la vie d\'église.', 'couleur' => '#64748b', 'ordre' => 50],
            ];

            foreach ($types as $type) {
                DB::table('types_statut_membres')->updateOrInsert(
                    ['mission_id' => $mission->id, 'code' => $type['code']],
                    array_merge($type, [
                        'actif' => true,
                        'is_system' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            }
        }

        $membres = DB::table('membres')
            ->join('eglises_locales', 'eglises_locales.id', '=', 'membres.eglise_locale_id')
            ->select('membres.id', 'membres.actif', 'eglises_locales.mission_id')
            ->whereNull('membres.type_statut_membre_id')
            ->get();

        foreach ($membres as $membre) {
            $targetCode = ((bool) $membre->actif) ? 'actif' : 'refroidi';
            $type = DB::table('types_statut_membres')
                ->where('mission_id', $membre->mission_id)
                ->where('code', $targetCode)
                ->first();

            if ($type === null) {
                continue;
            }

            DB::table('membres')
                ->where('id', $membre->id)
                ->update(['type_statut_membre_id' => $type->id]);

            DB::table('membre_historique_statuts')->insert([
                'membre_id' => $membre->id,
                'type_statut_membre_id' => $type->id,
                'change_par_user_id' => null,
                'motif' => 'Initialisation automatique du statut membre.',
                'changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('membre_historique_statuts');

        Schema::table('membres', function (Blueprint $table) {
            $table->dropConstrainedForeignId('type_statut_membre_id');
        });

        Schema::dropIfExists('types_statut_membres');
    }
};
