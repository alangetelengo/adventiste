<?php

return [
    'pages' => [
        'utilisateurs' => 'Utilisateurs',
        'roles' => 'Rôles',
        'permissions' => 'Permissions',
        'types_recette' => 'Types de recette',
        'types_statut' => 'Types de statut membre',
        'tresorerie_lignes' => 'Lignes — ventilation trésorerie mission',
        'type_statut_new' => 'Nouveau type de statut membre',
        'type_statut_edit' => 'Modifier le type de statut membre',
        'type_recette_new' => 'Nouveau type de recette',
        'type_recette_edit' => 'Modifier le type de recette',
    ],
    'hub' => [
        'title' => 'Paramètres',
        'subtitle' => 'Configuration de la mission et données de référence',
        'cta_access' => 'Accéder au module',
        'cta_configure' => 'Configurer',
        'cta_manage' => 'Gérer',
        'restricted_title' => 'Accès restreint',
        'restricted_eglises' => 'La gestion des églises est réservée aux comptes mission (sans rattachement à une église locale). Contactez votre administrateur.',
        'coming_title' => 'À venir',
        'coming_body' => 'D’autres réglages avancés seront regroupés ici.',
        'cards' => [
            'eglises' => [
                'title' => 'Églises locales',
                'desc' => 'Annuaire des paroisses : codes uniques, rattachement district, indicateurs financiers de référence.',
            ],
            'districts' => [
                'title' => 'Districts',
                'desc' => 'Découpage territorial : rattachez chaque église locale à un district.',
            ],
            'groupes_mission' => [
                'title' => 'Groupes mission',
                'desc' => 'Petits groupes et services au niveau mission : codes uniques, rattachement des membres et ventilation financière.',
            ],
            'tresorerie_lignes' => [
                'title' => 'Lignes — ventilation trésorerie',
                'desc' => 'Ordre, libellés et pourcentages du rapport mensuel mission (remontée financière).',
            ],
            'types_recette' => [
                'title' => 'Types de recette',
                'desc' => 'Dîme, offrandes cultuelles, construction, ÉDS, dons — liés à la ventilation (dîme / offrande / don).',
            ],
            'types_statut' => [
                'title' => 'Types de statut membre',
                'desc' => 'Catégories pastorales des membres (actif, régulier, irrégulier, sous censure, refroidi, etc.).',
            ],
            'ventilation_recettes' => [
                'title' => 'Ventilation des recettes',
                'desc' => 'Pourcentages mission / église locale par type (dîme, offrande, don) pour les rapports mensuels et annuels.',
            ],
            'utilisateurs' => [
                'title' => 'Utilisateurs',
                'desc' => 'Comptes de la mission : rôles, accès église pour les trésoriers.',
            ],
            'roles' => [
                'title' => 'Rôles',
                'desc' => 'Rôles applicatifs et association aux permissions (RBAC).',
            ],
            'permissions' => [
                'title' => 'Permissions',
                'desc' => 'Granularité d’accès : créez et reliez les permissions aux rôles.',
            ],
        ],
    ],
    'nav' => [
        'aria' => 'Sous-menu paramètres',
        'overview' => 'Vue d’ensemble',
        'districts' => 'Districts',
        'groupes_mission' => 'Groupes mission',
        'tresorerie_lignes' => 'Ventilation trésorerie (lignes)',
        'types_recette' => 'Types de recette',
        'types_statut' => 'Types de statut membre',
        'ventilation_recettes' => 'Ventilation recettes',
        'eglises_ministeres' => 'Églises & Ministères',
        'utilisateurs' => 'Utilisateurs',
        'roles' => 'Rôles',
        'permissions' => 'Permissions',
    ],
];
