<?php

declare(strict_types=1);

use App\Controllers\Api\HealthController;

return [
    ['GET', '/api/v1/health', [HealthController::class, 'index']],
];
