<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'options' => [
        'timeout' => 30,
        'allowed_methods' => [
            'POST',
            'PUT',
            'DELETE'
        ],
        'exclude_routes' => [
            'search',
            'default_program',
            'admin.*'
        ]
    ],
    'enable' => env('NOCAPTCHA_ENABLE', true),
];