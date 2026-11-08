<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'default' => Env::get('APP_THEME', 'dark'),
    'brand' => [
        'primary' => Env::get('BRAND_PRIMARY', ''),
        'accent' => Env::get('BRAND_ACCENT', ''),
        'background' => Env::get('BRAND_BACKGROUND', ''),
        'surface' => Env::get('BRAND_SURFACE', ''),
    ],
];
