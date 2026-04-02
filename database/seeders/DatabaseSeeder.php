<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EglisesLocalesEtatDimes2025Seeder::class,
            DistrictsBrazzavilleImportSeeder::class,
            MembresFromExportJsonSeeder::class,
            TresorerieTalangaiJanMars2026Seeder::class,
            DonneesDemoSeeder::class,
            ComptesDirectionMissionMcBSeeder::class,
        ]);
    }
}
