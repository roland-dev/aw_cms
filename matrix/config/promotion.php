<?php

return [
    'moveqr' => [
        'max_fans' => env('MOVEQR_MAX_FANS', 200),
        'base_url' => env('MOVEQR_BASE_URL', ''),
        'file_list' => env('MOVEQR_FILE_LIST', ''),

        'commission1st' => [
            'max_fans' => env('MOVEQR_COMM1ST_MAX_FANS', 200),
            'file_list' => env('MOVEQR_COMM1ST_FILE_LIST', ''),
        ],
        'commission2nd' => [
            'max_fans' => env('MOVEQR_COMM2ND_MAX_FANS', 200),
            'file_list' => env('MOVEQR_COMM2ND_FILE_LIST', ''),
        ],
        'commission3rd' => [
            'max_fans' => env('MOVEQR_COMM3RD_MAX_FANS', 200),
            'file_list' => env('MOVEQR_COMM3RD_FILE_LIST', ''),
        ],
        'commission4th' => [
            'max_fans' => env('MOVEQR_COMM4TH_MAX_FANS', 200),
            'file_list' => env('MOVEQR_COMM4TH_FILE_LIST', ''),
        ],
    ],
];
