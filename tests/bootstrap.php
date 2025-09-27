<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if ('test' === $_SERVER['APP_ENV']) {
    $kernel = new App\Kernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);

    try {
        $application->run(new ArrayInput([
            'command' => 'doctrine:query:sql',
            'sql'     => "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = 'app_test' AND pid <> pg_backend_pid();",
            '--env'   => 'test',
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
            '--if-exists' => true,
            '--env' => 'test',
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
            '--if-not-exists' => true,
            '--env' => 'test',
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
    } catch (\Throwable $e) {
        throw new \RuntimeException('Fixtures load failed: ' . $e->getMessage());
    }
}
