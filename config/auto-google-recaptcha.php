<?php

return [
    'sitekey' => env('NOCAPTCHA_SITEKEY', ''),
    'secret' => env('NOCAPTCHA_SECRET', ''),
    'options' => [
        'timeout' => 30,

        // Methods requiring captcha
        'allowed_methods' => [
            'POST',
            'PUT',
            'DELETE'
        ],

        // Routes to exclude from captcha
        // Note: For Laravel, Add route name with wildcards (eg. post.create, post.edit, post.*) 
        // Note: For PHP, Add url paths to exclude with wildcards (eg. /post/create, /post/edit, *post*) 
        'excluded_routes' => [
            'admin.*' // supports wildcards
        ],

        // Enable/disable globally
        'enable' => env('NOCAPTCHA_ENABLE', true),
    ],
];