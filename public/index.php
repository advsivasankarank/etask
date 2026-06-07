<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('BASE_PATH', ROOT_PATH);

require ROOT_PATH . '/app/Helpers/helpers.php';

$router = require ROOT_PATH . '/bootstrap/app.php';
require ROOT_PATH . '/routes/web.php';

$router->dispatch(new \App\Core\Request());
