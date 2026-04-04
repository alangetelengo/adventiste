<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Garantit les statuts « Régulier » et « Irrégulier » pour chaque mission
 * (ex. mission créée après les migrations initiales ou types supprimés puis à recréer).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $missions = DB::table('missions')->select('id')->get();

        $types = [
            [
                'code' => 'regulier',
                'libelle' => 'Régulier',
                'description' => 'Membre en règle selon les principes de foi et la discipline ecclésiale.',
                'couleur' => '#2563eb',
                'ordre' => 20,
            ],
            [
                'code' => 'irregulier',
                'libelle' => 'Irrégulier',
                'description' => 'Situation nécessitant un accompagnement pastoral.',
                'couleur' => '#ea580c',
                'ordre' => 30,
            ],
        ];

        foreach ($missions as $mission) {
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
    }

    public function down(): void
    {
        // No-op : ne pas supprimer des statuts potentiellement utilisés.
    }
};
