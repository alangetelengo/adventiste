<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\EgliseLocale;
use App\Models\Mission;
use Illuminate\Database\Seeder;

/**
 * Districts issus du dump SQL (mission_id=1 dans la source → Mission du Congo Brazzaville).
 * Rattache l’église MCB-TALANGAI au district Nord (Brazzaville), où se trouve l’église des membres importés.
 */
class DistrictsBrazzavilleImportSeeder extends Seeder
{
    public function run(): void
    {
        $mission = Mission::query()->firstOrCreate(
            ['nom' => 'Mission du Congo Brazzaville'],
            ['nom_court' => 'MCB']
        );

        District::query()->updateOrCreate(
            [
                'mission_id' => $mission->id,
                'nom' => 'District Sud — Brazzaville',
            ],
            []
        );

        $nord = District::query()->updateOrCreate(
            [
                'mission_id' => $mission->id,
                'nom' => 'District Nord — Brazzaville',
            ],
            []
        );

        EgliseLocale::query()
            ->where('code_unique', 'MCB-TALANGAI')
            ->update(['district_id' => $nord->id]);
    }
}
