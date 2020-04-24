<?php

use Dotenv\Dotenv;
use Lefuturiste\Jobatator\Client;

require 'vendor/autoload.php';

if (file_exists('./.env')) {
 (new Dotenv(__DIR__))->load();
}

$container = new DI\Container();

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(include './src/config/config.php');
$builder->addDefinitions(include './src/config/containers.php');
$container = $builder->build();

if (getenv('SENTRY_DSN') !== null && is_string(getenv('SENTRY_DSN'))) {
    Sentry\init(['dsn' => getenv('SENTRY_DSN') ]);
}

$client = $container->get(Client::class);

$client->setRootValue($container);

include 'src/handlers.php';

echo "> Worker initialized, now ready to work... \n";

if (boolval(getenv("SENTRY_ENABLE")))
    $client->addExceptionHandler(fn ($e) => Sentry\captureException($e));

$client->addExceptionHandler(function ($e) {
    throw $e;
});

$client->startWorker();
