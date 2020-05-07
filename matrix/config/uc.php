<?php

return [
    'url' => env('URL_UC', 'http://api.zhongyingtougu.com'),
    'guard' => [
        'default' => [
            'algo' => env('UC_GUARD_ALGO', 'sha256'),
            'siteKey' => env('UC_GUARD_SITE_KEY', 'uc-app-mesh-6dcd84ce9'),
            'siteSecret' => env('UC_GUARD_SITE_SECRET', 'e40ed27f7c24eenys89971e7dcd84ce9'),
        ],
        'hk' => [
            'algo' => env('UC_GUARD_HK_ALGO', 'sha256'),
            'siteKey' => env('UC_GUARD_HK_SITE_KEY', 'rfzqb3a8e0f4aa7da7db2fed317e8d4536bb'),
            'siteSecret' => env('UC_GUARD_HK_SITE_SECRET', '7d670665c442db2fed317e52b011b38f62063e5de'),
        ],
    ],
    'session' => [
        'lifetime' => env('UC_SESSION_LIFE', 600), // FBI Warning: It must be minutes!
    ],
];
