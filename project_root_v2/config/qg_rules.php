<?php
return [
    'validation_rules' => [
        'C1' => [
            'required' => ['qg1', 'qg2'],
            'approval_threshold' => 2 // Les 2 QG doivent approuver
        ],
        'C2' => [
            'required' => ['qg1'],
            'approval_threshold' => 1
        ]
    ],
    'comment_requirements' => [
        'rejection' => 'Un commentaire est obligatoire pour un rejet',
        'approval' => 'Un commentaire est recommandé pour une approbation'
    ]
];