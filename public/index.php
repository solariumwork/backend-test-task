<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $env = (isset($context['APP_ENV']) && is_string($context['APP_ENV'])) ? $context['APP_ENV'] : 'dev';
    $debug = isset($context['APP_DEBUG']) ? (bool) $context['APP_DEBUG'] : false;

    return new Kernel($env, $debug);
};
