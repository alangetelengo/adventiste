<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificat de baptême</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            color: #0f172a;
            background: #f5f6f8;
            padding: 22px;
        }
        .toolbar {
            max-width: 1200px;
            margin: 0 auto 14px auto;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            text-decoration: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-primary {
            background: #059669;
            border-color: #047857;
            color: #fff;
        }
        .btn-primary:hover {
            background: #047857;
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d7dce3;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
            page-break-after: always;
            break-after: page;
        }
        .page:last-of-type {
            page-break-after: auto;
            break-after: auto;
        }

        .diamond-frame {
            border: 2px solid #c8c8c8;
            padding: 14px;
            position: relative;
            background: #fff;
        }
        .diamond-frame::before,
        .diamond-frame::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 12px;
            background:
                linear-gradient(45deg, #d9d9d9 25%, transparent 25%) -8px 0/16px 16px,
                linear-gradient(-45deg, #2f2f2f 25%, transparent 25%) -8px 0/16px 16px,
                linear-gradient(45deg, transparent 75%, #b7b7b7 75%) -8px 0/16px 16px,
                linear-gradient(-45deg, transparent 75%, #4a4a4a 75%) -8px 0/16px 16px;
        }
        .diamond-frame::before { top: 0; }
        .diamond-frame::after { bottom: 0; }

        .page-one-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .card {
            border: 3px solid #303030;
            min-height: 100%;
            padding: 20px 24px;
            position: relative;
        }
        .card-engagement {
            border: 1px solid #cfd3d8;
            padding: 14px 16px;
            background: #fff;
        }

        .title-main {
            margin: 0 0 8px 0;
            font-size: 34px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.6px;
        }
        .title-upper { text-transform: uppercase; }
        .small { margin: 2px 0; font-size: 14px; }

        .engagement-title {
            margin: 2px 0 8px 0;
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .engagement {
            margin: 0;
            font-size: 14px;
            line-height: 1.42;
            padding-left: 18px;
        }
        .engagement li { margin-bottom: 5px; }
        .quote {
            margin-top: 10px;
            font-size: 12px;
            text-align: center;
            font-style: italic;
        }

        .line-block { margin-top: 12px; font-size: 30px; line-height: 1.95; }
        .field {
            display: inline-block;
            min-width: 520px;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px 4px;
            font-weight: 700;
        }
        .field-sm {
            display: inline-block;
            min-width: 360px;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px 4px;
            font-weight: 700;
        }
        .field-xs {
            display: inline-block;
            min-width: 220px;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px 4px;
            font-weight: 700;
        }

        .cert-title {
            margin: 14px 0 10px 0;
            font-size: 54px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .institution {
            text-align: center;
            line-height: 1.35;
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 17px;
        }
        .institution strong {
            font-size: 20px;
        }
        .institution-with-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .institution-logo {
            width: 86px;
            height: 86px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .institution-text {
            text-align: center;
            line-height: 1.35;
        }

        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0.08;
            font-size: 240px;
            font-weight: 700;
            color: #3b82f6;
            z-index: 0;
        }
        .card-content { position: relative; z-index: 1; }

        .signature-row {
            margin-top: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
            font-size: 28px;
        }
        .sig-line {
            margin-top: 60px;
            border-top: 1px solid #111827;
            padding-top: 5px;
            text-align: center;
        }
        .divider {
            margin: 20px 0;
            border-top: 1px dashed #cbd5e1;
        }

        .admission-title {
            margin-top: 12px;
            text-align: center;
            font-size: 34px;
            text-transform: uppercase;
            border-top: 6px solid #222;
            padding-top: 10px;
            font-weight: 700;
        }

        .doctrines-title {
            margin: 2px 0 12px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .doctrine-columns {
            column-count: 2;
            column-gap: 22px;
            font-size: 14px;
            line-height: 1.34;
        }
        .doctrine-columns p {
            margin: 0 0 8px 0;
            break-inside: avoid;
        }
        .doctrine-columns strong {
            font-weight: 700;
        }
        .meta {
            margin-top: 10px;
            text-align: right;
            font-size: 12px;
            color: #475569;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
            }

            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .page {
                width: 285mm;
                height: 198mm;
                max-width: none;
                margin: 0 0 2mm 0;
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 4mm;
                overflow: hidden;
                page-break-after: always;
                break-after: page;
            }
            .page:last-of-type {
                page-break-after: auto;
                break-after: auto;
            }

            .diamond-frame {
                border-width: 1px;
                padding: 6px;
            }
            .diamond-frame::before,
            .diamond-frame::after {
                height: 7px;
                background-size: 12px 12px, 12px 12px, 12px 12px, 12px 12px;
            }

            .page-one-grid {
                gap: 8px;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .card,
            .card-engagement {
                min-height: auto;
                padding: 8px 10px;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .engagement-title {
                font-size: 20px;
                margin-bottom: 4px;
            }
            .engagement {
                font-size: 10px;
                line-height: 1.24;
                padding-left: 12px;
            }
            .engagement li {
                margin-bottom: 2px;
            }
            .quote {
                font-size: 8px;
                margin-top: 4px;
            }

            .institution {
                font-size: 10px;
                margin: 1px 0 3px 0;
            }
            .institution strong {
                font-size: 12px;
            }
            .institution-with-logo {
                gap: 8px;
                margin: 1px 0 3px 0;
            }
            .institution-logo {
                width: 54px;
                height: 54px;
            }
            .cert-title {
                font-size: 18px;
                margin: 6px 0 5px 0;
                letter-spacing: 0.3px;
            }
            .line-block {
                font-size: 9.5px;
                line-height: 1.34;
                margin-top: 5px;
            }
            .field { min-width: 170px; }
            .field-sm { min-width: 120px; }
            .field-xs { min-width: 72px; }

            .admission-title {
                font-size: 13px;
                border-top-width: 2px;
                padding-top: 4px;
                margin-top: 6px;
            }
            .signature-row {
                margin-top: 6px;
                font-size: 9px;
                gap: 8px;
            }
            .sig-line {
                margin-top: 10px;
                padding-top: 2px;
            }
            .watermark {
                font-size: 88px;
                opacity: 0.05;
            }

            .doctrines-title {
                font-size: 10px;
                margin: 1px 0 4px;
            }
            .doctrine-columns {
                font-size: 7px;
                line-height: 1.16;
                column-gap: 8px;
            }
            .doctrine-columns p {
                margin-bottom: 2px;
            }
            .meta {
                font-size: 7px;
                margin-top: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn btn-primary" onclick="window.print()">Imprimer</button>
        <a class="btn" href="{{ route('baptemes.show', $bapteme) }}">Retour fiche</a>
    </div>

    <section class="page diamond-frame">
        <div class="page-one-grid">
            <div class="card-engagement">
                <h1 class="engagement-title">Mon engagement</h1>
                <ol class="engagement">
                    <li>Je crois qu'il existe un seul Dieu : Père, Fils et Saint Esprit, une unité de trois personnes également éternelles.</li>
                    <li>J'accepte la mort de Jésus-Christ au calvaire comme le sacrifice expiatoire pour mes péchés et je crois que par la grâce de Dieu, au moyen de la foi en son sang versé, je suis sauvé du péché et de sa conséquence.</li>
                    <li>J'accepte Jésus Christ comme mon Seigneur et Sauveur personnel et je crois que Dieu, en Christ a pardonné mes péchés et m'a donné un cœur nouveau, et je renonce aux mauvais chemins de ce monde.</li>
                    <li>J'accepte par la foi la justice du Christ, mon intercesseur dans le sanctuaire céleste, et je crois en sa promesse de me donner sa grâce qui transforme et la capacité à vivre une vie aimante et centrée sur Christ en privé comme en public.</li>
                    <li>Je crois que la Bible est la Parole inspirée de Dieu, la seule règle de foi et de pratique pour le chrétien. Je m'engage à passer du temps régulièrement dans la prière et l'étude de la Bible.</li>
                    <li>Je reconnais dans les dix commandements une transcription du caractère de Dieu et une révélation de sa volonté. J'ai l'intention, grâce à la puissance du Christ qui vit en moi, de garder cette loi, y compris le quatrième commandement qui demande d'observer le septième jour de la semaine comme le sabbat du Seigneur et le mémorial de la création.</li>
                    <li>J'attends la venue imminente de Jésus et j'ai l'espoir que bientôt ce qui est mortel revêtira l'immortalité. En me préparant à rencontrer le Seigneur, je veux témoigner de la bonté de son salut en employant mes capacités à gagner des êtres humains à lui, afin de les aider à être prêts pour sa glorieuse apparition.</li>
                    <li>J'accepte l'enseignement biblique au sujet des dons spirituels et je crois que le don de prophétie est l'une des marques distinctives de l'Église du reste.</li>
                    <li>Je crois en l'organisation de l'Église. J'ai l'intention de rendre un culte à Dieu et de soutenir l'Église par mes dîmes et mes offrandes, ainsi que par mes efforts et mon influence personnelle.</li>
                    <li>Je crois que mon corps est le temple du Saint Esprit, et je veux honorer Dieu en en prenant soin, évitant de faire usage de ce qui lui cause du tort. Je souhaite notamment m'abstenir de tout aliment impur, et je renonce à utiliser, fabriquer et vendre des boissons alcoolisées, du tabac sous toutes ses formes destinées à la consommation ; je renonce à faire un usage inapproprié des narcotiques et des autres drogues et je renonce à en faire le trafic.</li>
                    <li>Je connais et je comprends les principes bibliques de base, tels qu'ils sont enseignés par l'Église Adventiste du Septième jour. Je veux, par la grâce de Dieu, accomplir sa volonté en harmonisant ma vie avec ces principes.</li>
                    <li>J'accepte l'enseignement du nouveau Testament au sujet du baptême par immersion et je désire être baptisé de cette manière, afin d'exprimer publiquement la foi en Christ et son pardon de mes péchés.</li>
                    <li>Je suis convaincu que l'Église Adventiste du septième jour est l'église du reste de la prophétie biblique et que des gens de toute nation, race et langue sont invités et acceptés dans cette communauté. Je désire être membre de cette assemblée locale de l'Église mondiale.</li>
                </ol>
                <p class="quote">« Allez donc, enseignez toutes les nations, en les baptisant au nom du Père, du Fils et du Saint-Esprit, et en leur apprenant à garder tout ce que je vous ai commandé. Et voici je suis avec vous tous les jours jusqu'à la fin du monde » Mat. 28:18-20</p>
                <p class="quote">« Sois fidèle jusqu'à la mort, et je te donnerai la couronne de vie » Apoc. 2:10</p>
                <p class="quote"><strong>Nom et Signature du candidat</strong></p>
            </div>

            <div class="card">
                <div class="watermark">⛪</div>
                <div class="card-content">
                    <div class="institution-with-logo">
                        <img src="{{ asset('images/logo_adventiste.jpg') }}" alt="Logo Église Adventiste" class="institution-logo">
                        <div class="institution-text">
                            <div><strong>ÉGLISE DES ADVENTISTES DU SEPTIÈME JOUR</strong></div>
                            <div>Union Mission de l'Afrique Centrale</div>
                            <div><strong>MISSION DU CONGO</strong></div>
                        </div>
                    </div>
                    <h2 class="cert-title">Certificat de baptême</h2>

                    <div class="line-block">
                        <div>Nous certifions que selon l'ordre de notre Seigneur Jésus-Christ</div>
                        <div>Mr/Mme/Mlle <span class="field">{{ $bapteme->nom }} {{ $bapteme->prenom }}</span></div>
                        <div>
                            @if (($typesBapteme[$bapteme->type_bapteme] ?? '') === 'Profession de foi')
                                A été reçu(e) par profession de foi
                            @else
                                A été immergé(e)... dans les eaux du baptême
                            @endif
                        </div>
                        <div>A <span class="field-sm">{{ $bapteme->lieu_bapteme ?? '____________________' }}</span> Le <span class="field-xs">{{ $bapteme->date_bapteme?->format('d/m/Y') ?? '____/____/______' }}</span></div>
                        <div>Par le  <span class="field-sm">{{ $bapteme->officiant ?? '____________________' }}</span></div>
                        <div>De l'Église Adventiste de <span class="field-sm">{{ $bapteme->egliseLocale?->nom ?? '____________________' }}</span></div>
                        <div> <span class="field-sm">{{ $bapteme->egliseLocale?->district?->nom ?? '____________________' }}</span></div>
                        <div>Signature de l'Officiant <span class="field-sm"></span></div>
                    </div>

                    <div class="admission-title">Admission</div>
                    <div class="line-block" style="margin-top: 10px;">
                        <div>Reçu comme membre de l'Église Adventiste du septième jour</div>
                        <div>De: <span class="field-sm">{{ $bapteme->egliseLocale?->nom ?? '____________________' }}</span> Le: <span class="field-xs">{{ ($bapteme->membre?->date_admission_eglise ?? $bapteme->date_bapteme)?->format('d/m/Y') ?? '____/____/______' }}</span></div>
                    </div>

                    <div class="signature-row">
                        <div></div>
                        <div>
                            <div class="sig-line">Le Secrétaire d'Église</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="page diamond-frame">
        <h3 class="doctrines-title">Sommaire des doctrines fondamentales des Adventistes du jour</h3>
        <div class="doctrine-columns">
            <p><strong>1.</strong> Le Dieu vivant et véritable, la première personne de la divinité, est notre Père céleste. Il a créé toutes choses par son Fils, le Christ Jésus.</p>
            <p><strong>2.</strong> Jésus Christ, la deuxième personne de la divinité, le Fils éternel de Dieu est le seul qui sauve du péché. Le salut de l'homme vient de la grâce, au travers de la foi en lui.</p>
            <p><strong>3.</strong> Le Saint Esprit est la troisième personne de la divinité. Il conduit les pécheurs à la repentance et à l'obéissance.</p>
            <p><strong>4.</strong> Par le Christ, les croyants reçoivent le pardon des péchés qu'ils ont laissés et confessés et qu'ils s'efforcent de réparer.</p>
            <p><strong>5.</strong> La Bible est la parole inspirée de Dieu, elle est l'unique règle fondamentale de foi et de pratique.</p>
            <p><strong>6.</strong> Quiconque entre dans le royaume des cieux doit avoir fait l'expérience de la conversion, ou nouvelle naissance.</p>
            <p><strong>7.</strong> Le Christ habite dans le cœur régénéré et y inscrit les principes de la loi de Dieu.</p>
            <p><strong>8.</strong> A son ascension, le Christ a inauguré son ministère de souverain sacrificateur dans le sanctuaire céleste.</p>
            <p><strong>9.</strong> Le retour du Christ est espérance de l'Église, le point culminant de l'Évangile et le but du plan de la rédemption.</p>
            <p><strong>10.</strong> A la seconde venue du Christ, les justes qui seront morts ressusciteront avec les justes vivants.</p>
            <p><strong>11.</strong> Les méchants vivants au retour du Christ seront tués par l'éclat de sa venue et attendront la seconde résurrection.</p>
            <p><strong>12.</strong> A la fin des mille ans : descente de la sainte Cité, jugement final des méchants, et destruction du péché par le feu.</p>
            <p><strong>13.</strong> La terre purifiée et renouvelée par la puissance de Dieu deviendra la demeure éternelle des rachetés.</p>
            <p><strong>14.</strong> Le septième jour de la semaine est le signe éternel de la puissance de Christ comme Créateur et Rédempteur.</p>
            <p><strong>15.</strong> Le mariage est une institution divine sacrée et pérenne ; la fidélité et la pureté sont requises par la Parole.</p>
            <p><strong>16.</strong> La dîme est consacrée au Seigneur pour soutenir son ministère ; les offrandes volontaires font aussi partie du plan divin.</p>
            <p><strong>17.</strong> L'immortalité est un don de Dieu accordé au retour du Christ.</p>
            <p><strong>18.</strong> Après la mort, l'homme est inconscient jusqu'à la résurrection.</p>
            <p><strong>19.</strong> Le chrétien est appelé à la sanctification, à la prudence, à la simplicité et à la modestie.</p>
            <p><strong>20.</strong> Le corps est le temple du Saint-Esprit ; il faut éviter ce qui est nuisible et toute dépendance destructive.</p>
            <p><strong>21.</strong> L'Église ne doit manquer d'aucun don ; le don de prophétie est un signe distinctif de l'Église du reste.</p>
            <p><strong>22.</strong> L'organisation biblique de l'Église doit être respectée et soutenue fidèlement par ses membres.</p>
            <p><strong>23.</strong> Le baptême par immersion exprime la foi en Christ et est une condition d'accès au statut de membre d'Église.</p>
            <p><strong>24.</strong> La sainte cène commémore la mort du Christ et doit être précédée de l'ablution des pieds.</p>
            <p><strong>25.</strong> La vie chrétienne implique l'abandon des pratiques mondaines qui détruisent la vie spirituelle.</p>
            <p><strong>26.</strong> L'étude de la Parole et la prière sont indispensables pour la victoire sur le péché et la croissance spirituelle.</p>
            <p><strong>27.</strong> Chaque membre doit employer ses talents à gagner des âmes et participer à la proclamation de l'Évangile.</p>
            <p><strong>28.</strong> Dieu avertit le monde de son prochain retour ; ce message est symbolisé par les trois anges d'Apocalypse 14.</p>
        </div>
    </section>
</body>
</html>

