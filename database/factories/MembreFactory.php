<?php

namespace Database\Factories;

use App\Models\EgliseLocale;
use App\Models\Membre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Membre>
 */
class MembreFactory extends Factory
{
    protected $model = Membre::class;

    public function definition(): array
    {
        return [
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => null,
            'groupe_mission_id' => null,
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'sexe' => null,
            'date_naissance' => null,
            'lieu_naissance' => null,
            'noms_pere' => null,
            'noms_mere' => null,
            'adresses' => null,
            'telephone' => null,
            'niveau_etudes' => null,
            'occupation' => null,
            'situation_matrimoniale' => null,
            'date_mariage' => null,
            'conjoint' => null,
            'date_bapteme' => null,
            'lieu_bapteme' => null,
            'religion_anterieure' => null,
            'recu_dans_eglise_de' => null,
            'recu_le' => null,
            'baptise_par' => null,
            'observations' => null,
        ];
    }

    public function forEglise(EgliseLocale $eglise): static
    {
        return $this->state(fn () => [
            'eglise_locale_id' => $eglise->id,
        ]);
    }
}
