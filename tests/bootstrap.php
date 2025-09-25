<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if ('test' === $_SERVER['APP_ENV']) {
    $kernel = new App\Kernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $application->run(new ArrayInput([
        'command' => 'doctrine:database:create',
        '--env' => 'test',
        '--if-not-exists' => true,
    ]));

    $application->run(new ArrayInput([
        'command' => 'doctrine:migrations:migrate',
        '--env' => 'test',
        '--no-interaction' => true,
    ]));

    $application->run(new ArrayInput([
        'command' => 'doctrine:fixtures:load',
        '--env' => 'test',
        '--group' => ['test'],
        '--no-interaction' => true,
    ]));
}
