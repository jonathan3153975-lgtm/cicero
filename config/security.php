<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'csrf_token_name' => '_token',
    'login_max_attempts' => Env::int('LOGIN_MAX_ATTEMPTS', 5),
    'login_lockout_seconds' => Env::int('LOGIN_LOCKOUT_SECONDS', 300),
    'demo_email' => Env::get('DEMO_EMAIL', ''),
    'demo_password_hash' => Env::get('DEMO_PASSWORD_HASH', ''),
];
