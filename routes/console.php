<?php

use App\Models\Mission;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\NotificationInterne;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:rappels-ventilation-mission', function () {
    $today = Carbon::today();
    $annee = (int) $today->year;
    $moisMax = max(0, ((int) $today->month) - 1); // On rappelle seulement les mois déjà écoulés.

    if ($moisMax <= 0) {
        $this->info('Aucun mois écoulé à contrôler.');
        return;
    }

    Carbon::setLocale('fr');
    $created = 0;

    $missions = Mission::query()->get(['id', 'nom']);
    foreach ($missions as $mission) {
        for ($mois = 1; $mois <= $moisMax; $mois++) {
            $rapport = MissionTresorerieRapportMensuel::query()
                ->where('mission_id', (int) $mission->id)
                ->where('annee', $annee)
                ->where('mois', $mois)
                ->first();

            $etat = (string) ($rapport?->etat_transmission ?? MissionTresorerieRapportMensuel::ETAT_BROUILLON);
            $periodeReglee = in_array($etat, [
                MissionTresorerieRapportMensuel::ETAT_SOUMIS,
                MissionTresorerieRapportMensuel::ETAT_VALIDE_MISSION,
            ], true);
            if ($periodeReglee) {
                continue;
            }

            $periode = Carbon::createFromDate($annee, $mois, 1)
                ->locale('fr')
                ->translatedFormat('F/Y');
            $url = route('finances.ventilation-tresorerie-mission.edit', ['annee' => $annee, 'mois' => $mois]);
            $destinataires = User::query()
                ->where('mission_id', (int) $mission->id)
                ->whereNull('eglise_locale_id')
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['tresorier_mission', 'admin_mission']))
                ->get(['id']);

            foreach ($destinataires as $user) {
                // Anti-duplication: un rappel par utilisateur/période/mois calendaire.
                $alreadySent = NotificationInterne::query()
                    ->where('user_id', (int) $user->id)
                    ->where('type', 'ventilation_mission_rappel_periode')
                    ->where('url', $url)
                    ->whereDate('created_at', '>=', $today->copy()->startOfMonth()->toDateString())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                NotificationInterne::query()->create([
                    'user_id' => (int) $user->id,
                    'type' => 'ventilation_mission_rappel_periode',
                    'title' => 'Rappel: période de ventilation non soumise',
                    'message' => 'La période '.$periode.' n’est pas encore soumise (mission '.$mission->nom.').',
                    'url' => $url,
                    'data' => [
                        'mission_id' => (int) $mission->id,
                        'annee' => $annee,
                        'mois' => $mois,
                        'etat_actuel' => $etat,
                    ],
                ]);
                $created++;
            }
        }
    }

    $this->info('Rappels créés: '.$created);
})->purpose('Envoie les rappels de périodes non soumises (ventilation mission)');

Schedule::command('notifications:rappels-ventilation-mission')
    ->weekdays()
    ->dailyAt('08:00')
    ->withoutOverlapping();
