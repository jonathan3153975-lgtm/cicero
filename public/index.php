<?php

declare(strict_types=1);

use App\Support\Application;
use App\Support\Request;

/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$app->registerErrorHandling();
$app->handle(Request::capture())->send();
