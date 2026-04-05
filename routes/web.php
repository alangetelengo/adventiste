<?php

use App\Http\Controllers\BaptemeController;
use App\Http\Controllers\Finances\EtatDimesEglisesMissionController;
use App\Http\Controllers\Finances\RapportStationMissionController;
use App\Http\Controllers\Finances\SyntheseAnnuelleMissionController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MembreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Parametres\DepartementMinistereController;
use App\Http\Controllers\Parametres\DistrictController;
use App\Http\Controllers\Parametres\EgliseLocaleController;
use App\Http\Controllers\Parametres\EntreeFinanciereGroupeMissionController;
use App\Http\Controllers\Parametres\GroupeMissionController;
use App\Http\Controllers\Parametres\MissionTresorerieVentilationLignesController;
use App\Http\Controllers\Parametres\MissionVentilationRecettesController;
use App\Http\Controllers\Parametres\PermissionController;
use App\Http\Controllers\Parametres\RoleController;
use App\Http\Controllers\Parametres\TypeRecetteMissionController;
use App\Http\Controllers\Parametres\TypeStatutMembreController;
use App\Http\Controllers\Parametres\UtilisateurMissionController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\RapportMembreEgliseController;
use App\Http\Controllers\RapportMensuelEgliseController;
use App\Http\Controllers\RecapSabbatEgliseController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TableauDeBordController;
use App\Http\Controllers\VentilationTresorerieMissionController;
use App\Models\GroupeMission;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('locale.switch');

Route::middleware('guest')->get('/connexion', fn () => redirect()->route('login'));

require __DIR__.'/auth.php';

Route::get('/manifest.webmanifest', PwaManifestController::class)->name('pwa.manifest');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('tableau-de-bord');
    })->name('home');

    Route::get('/tableau-de-bord', TableauDeBordController::class)->name('tableau-de-bord');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profil/mot-de-passe', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/sync/csrf', [SyncController::class, 'csrf'])->name('sync.csrf');

    Route::prefix('finances')->name('finances.')->group(function () {
        Route::get('/recaps-sabbat', [RecapSabbatEgliseController::class, 'index'])->name('recaps.index');
        Route::get('/recaps-sabbat/nouveau', [RecapSabbatEgliseController::class, 'create'])->name('recaps.create');
        Route::post('/recaps-sabbat', [RecapSabbatEgliseController::class, 'store'])->name('recaps.store');
        Route::get('/recaps-sabbat/contribution-membre/{membre}', [RecapSabbatEgliseController::class, 'createContributionMembre'])->name('recaps.contribution-membre');
        Route::post('/recaps-sabbat/contribution-membre/{membre}', [RecapSabbatEgliseController::class, 'storeContributionMembre'])->name('recaps.contribution-membre.store');
        Route::get('/recaps-sabbat/{recap}/modifier', [RecapSabbatEgliseController::class, 'edit'])->name('recaps.edit');
        Route::put('/recaps-sabbat/{recap}', [RecapSabbatEgliseController::class, 'update'])->name('recaps.update');
        Route::post('/recaps-sabbat/{recap}/soumettre', [RecapSabbatEgliseController::class, 'soumettre'])->name('recaps.soumettre');
        Route::post('/recaps-sabbat/{recap}/accepter-mission', [RecapSabbatEgliseController::class, 'accepterMission'])->name('recaps.accepter-mission');
        Route::post('/recaps-sabbat/{recap}/refuser-mission', [RecapSabbatEgliseController::class, 'refuserMission'])->name('recaps.refuser-mission');

        Route::get('/rapports-mensuels', [RapportMensuelEgliseController::class, 'index'])->name('rapports-mensuels.index');
        Route::get('/rapports-mensuels/nouveau', [RapportMensuelEgliseController::class, 'create'])->name('rapports-mensuels.create');
        Route::post('/rapports-mensuels', [RapportMensuelEgliseController::class, 'store'])->name('rapports-mensuels.store');
        Route::get('/rapports-mensuels/{rapport}', [RapportMensuelEgliseController::class, 'show'])->name('rapports-mensuels.show');
        Route::get('/rapports-mensuels/{rapport}/modifier', [RapportMensuelEgliseController::class, 'edit'])->name('rapports-mensuels.edit');
        Route::put('/rapports-mensuels/{rapport}', [RapportMensuelEgliseController::class, 'update'])->name('rapports-mensuels.update');
        Route::delete('/rapports-mensuels/{rapport}', [RapportMensuelEgliseController::class, 'destroy'])->name('rapports-mensuels.destroy');
        Route::post('/rapports-mensuels/{rapport}/regenerer', [RapportMensuelEgliseController::class, 'regenerer'])->name('rapports-mensuels.regenerer');
        Route::post('/rapports-mensuels/{rapport}/soumettre', [RapportMensuelEgliseController::class, 'soumettre'])->name('rapports-mensuels.soumettre');
        Route::post('/rapports-mensuels/{rapport}/valider-mission', [RapportMensuelEgliseController::class, 'validerMission'])->name('rapports-mensuels.valider-mission');
        Route::post('/rapports-mensuels/{rapport}/refuser-mission', [RapportMensuelEgliseController::class, 'refuserMission'])->name('rapports-mensuels.refuser-mission');

        Route::get('/ventilation-tresorerie-mission', [VentilationTresorerieMissionController::class, 'index'])
            ->name('ventilation-tresorerie-mission.index');
        Route::get('/ventilation-tresorerie-mission/{annee}/{mois}', [VentilationTresorerieMissionController::class, 'edit'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.edit');
        Route::put('/ventilation-tresorerie-mission/{annee}/{mois}', [VentilationTresorerieMissionController::class, 'update'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.update');
        Route::post('/ventilation-tresorerie-mission/{annee}/{mois}/soumettre', [VentilationTresorerieMissionController::class, 'soumettre'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.soumettre');
        Route::post('/ventilation-tresorerie-mission/{annee}/{mois}/valider-mission', [VentilationTresorerieMissionController::class, 'validerMission'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.valider-mission');
        Route::post('/ventilation-tresorerie-mission/{annee}/{mois}/refuser-mission', [VentilationTresorerieMissionController::class, 'refuserMission'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.refuser-mission');
        Route::get('/ventilation-tresorerie-mission/{annee}/{mois}/impression', [VentilationTresorerieMissionController::class, 'impression'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.impression');
        Route::get('/ventilation-tresorerie-mission/{annee}/{mois}/export-pdf', [VentilationTresorerieMissionController::class, 'exportPdf'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.export-pdf');
        Route::get('/ventilation-tresorerie-mission/{annee}/{mois}/export-excel', [VentilationTresorerieMissionController::class, 'exportExcel'])
            ->whereNumber(['annee', 'mois'])
            ->name('ventilation-tresorerie-mission.export-excel');

        Route::resource('rapports-station', RapportStationMissionController::class)
            ->parameters(['rapports-station' => 'rapport']);

        Route::get('/etat-dimes-eglises', [EtatDimesEglisesMissionController::class, 'index'])
            ->name('etat-dimes-eglises.index');
        Route::get('/etat-dimes-eglises/impression', [EtatDimesEglisesMissionController::class, 'impression'])
            ->name('etat-dimes-eglises.impression');
        Route::get('/etat-dimes-eglises/export-pdf', [EtatDimesEglisesMissionController::class, 'exportPdf'])
            ->name('etat-dimes-eglises.export-pdf');
        Route::get('/etat-dimes-eglises/export-excel', [EtatDimesEglisesMissionController::class, 'exportExcel'])
            ->name('etat-dimes-eglises.export-excel');

        Route::get('/synthese-annuelle-mission', [SyntheseAnnuelleMissionController::class, 'index'])
            ->name('synthese-annuelle-mission.index');
        Route::put('/synthese-annuelle-mission/{annee}', [SyntheseAnnuelleMissionController::class, 'update'])
            ->whereNumber('annee')
            ->name('synthese-annuelle-mission.update');
        Route::get('/synthese-annuelle-mission/impression', [SyntheseAnnuelleMissionController::class, 'impression'])
            ->name('synthese-annuelle-mission.impression');
        Route::get('/synthese-annuelle-mission/export-pdf', [SyntheseAnnuelleMissionController::class, 'exportPdf'])
            ->name('synthese-annuelle-mission.export-pdf');
        Route::get('/synthese-annuelle-mission/export-excel', [SyntheseAnnuelleMissionController::class, 'exportExcel'])
            ->name('synthese-annuelle-mission.export-excel');
    });

    Route::resource('membres', MembreController::class)
        ->parameters(['membres' => 'membre']);
    Route::post('membres/{membre}/change-statut', [MembreController::class, 'changeStatut'])
        ->name('membres.change-statut');
    Route::prefix('secretariat')->name('secretariat.')->group(function () {
        Route::get('rapports-membres/{rapport}/impression', [RapportMembreEgliseController::class, 'impression'])
            ->name('rapports-membres.impression');
        Route::post('rapports-membres/{rapport}/soumettre', [RapportMembreEgliseController::class, 'soumettre'])
            ->name('rapports-membres.soumettre');
        Route::post('rapports-membres/{rapport}/valider', [RapportMembreEgliseController::class, 'valider'])
            ->name('rapports-membres.valider');
        Route::post('rapports-membres/{rapport}/rejeter', [RapportMembreEgliseController::class, 'rejeter'])
            ->name('rapports-membres.rejeter');
        Route::resource('rapports-membres', RapportMembreEgliseController::class)
            ->parameters(['rapports-membres' => 'rapport']);
    });
    Route::get('baptemes/{bapteme}/certificat', [BaptemeController::class, 'certificat'])->name('baptemes.certificat');
    Route::resource('baptemes', BaptemeController::class)
        ->parameters(['baptemes' => 'bapteme']);

    Route::get('/ecole-du-sabbat', function () {
        return view('static', [
            'pageTitle' => __('static_pages.ecole_sabbat.title'),
            'pageDescription' => __('static_pages.ecole_sabbat.description'),
        ]);
    })->name('ecoles-sabbat');

    Route::get('/finances', function () {
        return redirect()->route('finances.recaps.index');
    })->name('finances');

    Route::get('/evenements', function () {
        return view('static', [
            'pageTitle' => __('static_pages.evenements.title'),
            'pageDescription' => __('static_pages.evenements.description'),
        ]);
    })->name('evenements');

    Route::get('/groupes', function () {
        $user = request()->user();
        if ($user->can('viewAny', GroupeMission::class)) {
            return redirect()->route('parametres.groupes-mission.index');
        }

        return view('static', [
            'pageTitle' => __('static_pages.groupes.title'),
            'pageDescription' => __('static_pages.groupes.description'),
        ]);
    })->name('groupes');

    Route::prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametresController::class, 'index'])->name('index');
        Route::resource('districts', DistrictController::class);
        Route::resource('groupes-mission', GroupeMissionController::class)
            ->parameters(['groupes-mission' => 'groupe']);

        Route::resource('groupes-mission.entrees', EntreeFinanciereGroupeMissionController::class)
            ->parameters(['groupes-mission' => 'groupe', 'entrees' => 'entree'])
            ->except(['show']);

        Route::resource('eglises', EgliseLocaleController::class);

        Route::resource('eglises.departements', DepartementMinistereController::class)
            ->parameters(['eglises' => 'eglise', 'departements' => 'departement']);

        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('utilisateurs', UtilisateurMissionController::class)
            ->parameters(['utilisateurs' => 'utilisateur'])
            ->except(['show']);

        Route::get('/ventilation-recettes', [MissionVentilationRecettesController::class, 'edit'])
            ->name('ventilation-recettes.edit');
        Route::put('/ventilation-recettes', [MissionVentilationRecettesController::class, 'update'])
            ->name('ventilation-recettes.update');

        Route::get('/tresorerie-ventilation-lignes', [MissionTresorerieVentilationLignesController::class, 'edit'])
            ->name('tresorerie-ventilation-lignes.edit');
        Route::put('/tresorerie-ventilation-lignes', [MissionTresorerieVentilationLignesController::class, 'update'])
            ->name('tresorerie-ventilation-lignes.update');

        Route::resource('types-recette', TypeRecetteMissionController::class)
            ->parameters(['types-recette' => 'type'])
            ->except(['show']);

        Route::resource('types-statut-membre', TypeStatutMembreController::class)
            ->parameters(['types-statut-membre' => 'type'])
            ->except(['show']);
    });
});
