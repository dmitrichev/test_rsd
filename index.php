<?php

use App\Application;

require_once __DIR__ . '/bootstrap/bootstrap.php';

$app = Application::instance();

$app->run();
