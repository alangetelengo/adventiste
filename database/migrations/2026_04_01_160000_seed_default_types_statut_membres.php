<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        // No-op: keep seeded status types.
    }
};
