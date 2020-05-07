<?php

return [
    'video' => [
        'url' => env('VIDEO_PLAY_URL'), 
        'h5_url' => env('H5_VIDEO_PLAY_URL'),
        'signin_format' => env('VIDEO_SIGNIN_FORMAT'),
    ],
    'history' => [
        'aes_iv' => env('HISTORY_AES_IV', ''),
        'aes_key' => env('HISTORY_AES_KEY', ''),
        'aes_cipher' => env('HISTORY_AES_CIPHER', ''),
    ],
];
