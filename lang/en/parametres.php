<?php

return [
    'pages' => [
        'utilisateurs' => 'Users',
        'roles' => 'Roles',
        'permissions' => 'Permissions',
        'types_recette' => 'Receipt types',
        'types_statut' => 'Member status types',
        'tresorerie_lignes' => 'Lines — mission treasury split',
        'type_statut_new' => 'New member status type',
        'type_statut_edit' => 'Edit member status type',
        'type_recette_new' => 'New receipt type',
        'type_recette_edit' => 'Edit receipt type',
    ],
    'hub' => [
        'title' => 'Settings',
        'subtitle' => 'Mission configuration and reference data',
        'cta_access' => 'Open module',
        'cta_configure' => 'Configure',
        'cta_manage' => 'Manage',
        'restricted_title' => 'Restricted access',
        'restricted_eglises' => 'Church management is reserved for mission accounts (not tied to a local church). Contact your administrator.',
        'coming_title' => 'Coming soon',
        'coming_body' => 'More advanced settings will be grouped here.',
        'cards' => [
            'eglises' => [
                'title' => 'Local churches',
                'desc' => 'Parish directory: unique codes, district link, reference financial indicators.',
            ],
            'districts' => [
                'title' => 'Districts',
                'desc' => 'Territorial structure: link each local church to a district.',
            ],
            'groupes_mission' => [
                'title' => 'Mission groups',
                'desc' => 'Small groups and services at mission level: unique codes, member assignment, and financial split.',
            ],
            'tresorerie_lignes' => [
                'title' => 'Lines — treasury split',
                'desc' => 'Order, labels, and percentages for the mission monthly report (financial roll-up).',
            ],
            'types_recette' => [
                'title' => 'Receipt types',
                'desc' => 'Tithe, worship offerings, building, Sabbath School, gifts — linked to split (tithe / offering / gift).',
            ],
            'types_statut' => [
                'title' => 'Member status types',
                'desc' => 'Pastoral member categories (active, regular, irregular, under discipline, cold, etc.).',
            ],
            'ventilation_recettes' => [
                'title' => 'Receipt split',
                'desc' => 'Mission / local church percentages by type (tithe, offering, gift) for monthly and annual reports.',
            ],
            'utilisateurs' => [
                'title' => 'Users',
                'desc' => 'Mission accounts: roles, church access for treasurers.',
            ],
            'roles' => [
                'title' => 'Roles',
                'desc' => 'Application roles and link to permissions (RBAC).',
            ],
            'permissions' => [
                'title' => 'Permissions',
                'desc' => 'Fine-grained access: create and assign permissions to roles.',
            ],
        ],
    ],
    'nav' => [
        'aria' => 'Settings sub-menu',
        'overview' => 'Overview',
        'districts' => 'Districts',
        'groupes_mission' => 'Mission groups',
        'tresorerie_lignes' => 'Treasury split (lines)',
        'types_recette' => 'Receipt types',
        'types_statut' => 'Member status types',
        'ventilation_recettes' => 'Receipt split',
        'eglises_ministeres' => 'Churches & Ministries',
        'utilisateurs' => 'Users',
        'roles' => 'Roles',
        'permissions' => 'Permissions',
    ],
];
