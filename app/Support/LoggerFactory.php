<?php

declare(strict_types=1);

namespace App\Support;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    public static function create(Config $config): LoggerInterface
    {
        $logger = new Logger((string) $config->get('app.name', 'app'));

        $path = (string) $config->get('app.log.path', storage_path('logs/app.log'));
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $levels = [
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
        ];

        $minimum = $levels[strtolower((string) $config->get('app.log.level', 'debug'))] ?? Level::Debug;
        $channel = strtolower((string) $config->get('app.log.channel', 'daily'));

        $handler = $channel === 'daily'
            ? new RotatingFileHandler($path, 14, $minimum)
            : new StreamHandler($path, $minimum);

        $logger->pushHandler($handler);

        return $logger;
    }
}
