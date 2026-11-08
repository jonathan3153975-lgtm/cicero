<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name' => Env::get('SESSION_NAME', 'academia_session'),
    'lifetime' => Env::int('SESSION_LIFETIME', 120),
    'secure' => Env::bool('SESSION_SECURE', false),
    'http_only' => Env::bool('SESSION_HTTP_ONLY', true),
    'same_site' => Env::get('SESSION_SAME_SITE', 'Lax'),
];
